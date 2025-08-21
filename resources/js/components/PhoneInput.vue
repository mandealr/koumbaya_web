<template>
  <div class="phone-input-wrapper">
    <input
      ref="phoneInput"
      v-model="phoneNumber"
      type="tel"
      class="form-control"
      :placeholder="placeholder"
    />
  </div>
</template>

<script>
import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';

export default {
  name: 'PhoneInput',
  props: {
    modelValue: {
      type: String,
      default: ''
    },
    placeholder: {
      type: String,
      default: 'Numéro de téléphone'
    },
    preferredCountries: {
      type: Array,
      default: () => ['ga', 'cm', 'ci', 'cg', 'cf', 'td', 'gq', 'bf', 'bj', 'tg', 'fr', 'ca']
    },
    initialCountry: {
      type: String,
      default: 'ga'
    }
  },
  data() {
    return {
      phoneNumber: this.modelValue,
      iti: null
    };
  },
  watch: {
    phoneNumber(newVal) {
      this.$emit('update:modelValue', newVal);
    },
    modelValue(newVal) {
      if (newVal !== this.phoneNumber) {
        this.phoneNumber = newVal;
      }
    }
  },
  mounted() {
    this.initializeIntlTelInput();
  },
  beforeUnmount() {
    if (this.iti) {
      this.iti.destroy();
    }
  },
  methods: {
    initializeIntlTelInput() {
      this.iti = intlTelInput(this.$refs.phoneInput, {
        preferredCountries: this.preferredCountries,
        initialCountry: this.initialCountry,
        separateDialCode: true,
        utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
        customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => {
          return selectedCountryPlaceholder;
        }
      });

      this.$refs.phoneInput.addEventListener('countrychange', () => {
        this.emitPhoneData();
      });

      this.$refs.phoneInput.addEventListener('input', () => {
        this.emitPhoneData();
      });
    },
    emitPhoneData() {
      if (this.iti) {
        const countryData = this.iti.getSelectedCountryData();
        const fullNumber = this.iti.getNumber();
        
        this.$emit('phone-change', {
          number: this.phoneNumber,
          fullNumber: fullNumber,
          countryCode: countryData.dialCode,
          countryIso2: countryData.iso2,
          isValid: this.iti.isValidNumber()
        });
      }
    },
    getNumber() {
      return this.iti ? this.iti.getNumber() : '';
    },
    isValidNumber() {
      return this.iti ? this.iti.isValidNumber() : false;
    },
    setNumber(number) {
      if (this.iti) {
        this.iti.setNumber(number);
      }
    }
  }
};
</script>

<style scoped>
.phone-input-wrapper {
  width: 100%;
}

.phone-input-wrapper :deep(.iti) {
  width: 100%;
}

.phone-input-wrapper :deep(.iti__flag-container) {
  background-color: transparent;
}

.phone-input-wrapper :deep(.iti__selected-flag) {
  padding: 0 10px;
}

.phone-input-wrapper :deep(.form-control) {
  padding-left: 80px;
  width: 100%;
  height: 45px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
}

.phone-input-wrapper :deep(.form-control:focus) {
  border-color: #4CAF50;
  box-shadow: 0 0 0 0.2rem rgba(76, 175, 80, 0.25);
}
</style>