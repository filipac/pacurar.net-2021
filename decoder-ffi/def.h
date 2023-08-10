#define FFI_SCOPE "BLOG"
#define FFI_LIB "/Users/filipac/Code/blog/wp-content/themes/pacurar2020/decoder-ffi/libdecode_struct.dylib"

struct SftPriceRet {
  uint64_t amount;
  uint64_t nonce;
  const char *token;
};

struct SftResult {
  bool error;
  const char *error_message;
  const struct SftPriceRet *item;
};

struct AdvertiseSpaceRet {
  const char *owner;
  uint64_t paid_amount;
  uint64_t paid_until;
  bool is_new;
};

struct SpaceResult {
  bool error;
  const char *error_message;
  const struct AdvertiseSpaceRet *item;
};


struct SftResult *decode_sft_price(const char *base64string);

struct SpaceResult *decode_advertise_space(const char *base64string);
