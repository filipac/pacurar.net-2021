"""Validate actual fixture protocol responses; requires jsonschema[format]==4.26.0."""

import copy
import json
import os
from pathlib import Path
import subprocess
import tempfile

from jsonschema import Draft202012Validator, FormatChecker


root = Path(__file__).resolve().parents[2]
with tempfile.TemporaryDirectory(prefix="health-mcp-contract-") as directory:
    contract = Path(directory) / "responses.json"
    subprocess.run(
        [os.environ.get("PHP_BINARY", "php"), "tests/mcp/run.php"],
        cwd=root,
        env={**os.environ, "HEALTH_MCP_CONTRACT_FILE": str(contract)},
        check=True,
    )
    data = json.loads(contract.read_text())

validators = {}
for tool in data["tools"]:
    schema = tool["outputSchema"]
    Draft202012Validator.check_schema(schema)
    validators[tool["name"]] = Draft202012Validator(schema, format_checker=FormatChecker())
    validators[tool["name"]].validate({"error": {"code": "upstream_unavailable", "message": "Unavailable"}})
    assert not validators[tool["name"]].is_valid({}), "Missing result fields must fail"

successful_tools = set()
for response in data["responses"]:
    validators[response["tool"]].validate(response["result"])
    if "error" not in response["result"]:
        successful_tools.add(response["tool"])
assert successful_tools == validators.keys(), "Exercise every successful output shape"

# Reject an incorrect measurement type and invalid date, not just missing fields.
metric = next(r["result"] for r in data["responses"] if r["tool"] == "health_metric" and "error" not in r["result"])
for field, invalid in [("value", True), ("date", "2026-02-30"), ("measured_at", "yesterday")]:
    broken = copy.deepcopy(metric)
    broken["observations"][0][field] = invalid
    assert not validators["health_metric"].is_valid(broken), f"Invalid {field} must fail"

# Nullable timestamps and clock-time values are valid observations too.
clock = copy.deepcopy(metric)
clock["observations"] = [{"date": "2026-09-15", "value": "2026-09-14T23:00:00+03:00", "measured_at": None}]
validators["health_metric"].validate(clock)

print(f"Validated {len(data['responses'])} protocol results against all six output schemas; negative cases passed.")
