const {
    AddressType,
    BigUIntType,
    BinaryCodec, BooleanType,
    FieldDefinition, Struct,
    StructType, TypeMapper,
} = require("@multiversx/sdk-core/out");
const BigNumber = require("bignumber.js");

// @ts-ignore
process.env["NODE_TLS_REJECT_UNAUTHORIZED"] = 0

// function getStructure() {
//     return new StructType('AdvertiseSpace', [
//         new FieldDefinition('owner', '', new AddressType()),
//         new FieldDefinition('paid_amount', '', new BigUIntType()),
//         new FieldDefinition('paid_until', '', new BigUIntType()),
//         new FieldDefinition('is_new', '', new BooleanType()),
//     ])
// }

let mapper = new TypeMapper()
let simpleTypesMap = mapper.closedTypesMap

let strucFields = []
const structName = process.argv[2];
const fields = JSON.parse(process.argv[3])

for (let field of fields) {
    if (simpleTypesMap.has(field.type))
    {
        strucFields.push(new FieldDefinition(field.name, '', simpleTypesMap.get(field.type)))
    }
    // todo: add support for other types
}

const structure = new StructType(structName, strucFields)

const input = process.argv[4];

const attributesBuffer = Buffer.from(input, 'base64');
const codec = new BinaryCodec();
const [decoded] = codec.decodeNested(attributesBuffer, structure);

let return_object = {}

for(let field of structure.getFieldsDefinitions()) {
    return_object[field.name] = decoded.getFieldValue(field.name)
}

console.log(JSON.stringify(return_object))

// return;
//
// const owner = decoded.getFieldValue('owner');
// const paid_amount = decoded.getFieldValue('paid_amount');
// const paid_until = decoded.getFieldValue('paid_until');
//
// console.log(JSON.stringify({
//     owner: owner.toString(),
//     paid_amount: parseFloat(paid_amount.div(new BigNumber(10).pow(6)).toString()),
//     paid_until: parseInt(paid_until.toString()),
//     is_new: decoded.getFieldValue('is_new'),
// }))

//pkg -t node16-linux-x64,node16-macos-arm64 --compress GZip decoder.js
