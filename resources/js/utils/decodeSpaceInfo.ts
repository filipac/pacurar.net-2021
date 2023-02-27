import {
    AddressType,
    BigUIntType,
    BinaryCodec,
    BooleanType,
    FieldDefinition,
    Struct,
    StructType
} from "@multiversx/sdk-core/out";
import BigNumber from "bignumber.js";

function getStructure() {
    return new StructType('AdvertiseSpace', [
        new FieldDefinition('owner', '', new AddressType()),
        new FieldDefinition('paid_amount', '', new BigUIntType()),
        new FieldDefinition('paid_until', '', new BigUIntType()),
        new FieldDefinition('is_new', '', new BooleanType()),
    ])
}

export type SpaceInfo = {
    name: string,
    owner: string,
    paid_amount: number,
    paid_until: number,
    is_new: boolean,
}

export const decodeSpaceInfo = (spaceInfo: string): SpaceInfo => {
    const attributesBuffer = Buffer.from(spaceInfo, 'base64');
    const codec = new BinaryCodec();
    const [decoded] = codec.decodeNested<Struct>(attributesBuffer, getStructure());
    const owner: BigNumber.Instance = decoded.getFieldValue('owner');
    const paid_amount: BigNumber.Instance = decoded.getFieldValue('paid_amount');
    const paid_until: BigNumber.Instance = decoded.getFieldValue('paid_until');

    return {
        name: '',
        owner: owner.toString(),
        paid_amount: parseFloat(paid_amount.div(new BigNumber(10).pow(6)).toString()),
        paid_until: parseInt(paid_until.toString()),
        is_new: decoded.getFieldValue('is_new'),
    }
}
