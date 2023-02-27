<?php

namespace App;

enum JwtParseResult: int
{
    case ERR_NONE = 0;
    case ERR_SESSION_ID_MISMATCH = 1;
    case ERR_AUDIENCE_MISMATCH = 2;
    case ERR_ISSUER_MISMATCH = 3;
    case ERR_EXPIRED = 4;
    case ERR_INVALID = 5;
}
