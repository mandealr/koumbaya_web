<template>
  <div class="phone-input-wrapper">
    <input
      ref="phoneInput"
      type="tel"
      class="admin-input"
      :placeholder="placeholder"
    />
  </div>
</template>

<script>
import intlTelInput from 'intl-tel-input';
import 'intl-tel-input/build/css/intlTelInput.css';

export default {
  name: 'PhoneInputAdmin',
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
      default: () => ['ga', 'cm', 'ci', 'cg', 'cf', 'td', 'gq', 'fr']
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
        if (this.iti && this.$refs.phoneInput && newVal) {
          this.iti.setNumber(newVal);
        }
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
      const input = this.$refs.phoneInput;
      
      this.iti = intlTelInput(input, {
        preferredCountries: this.preferredCountries,
        initialCountry: this.initialCountry,
        separateDialCode: true,
        customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => {
          return selectedCountryPlaceholder;
        }
      });

      input.addEventListener('input', (e) => {
        this.phoneNumber = e.target.value;
        this.emitAllData();
      });

      input.addEventListener('countrychange', () => {
        this.emitAllData();
      });
      
      setTimeout(() => {
        if (this.modelValue) {
          this.iti.setNumber(this.modelValue);
        }
        this.emitAllData();
      }, 100);
    },
    emitAllData() {
      if (this.iti && this.iti.getSelectedCountryData) {
        try {
          const countryData = this.iti.getSelectedCountryData();
          const rawNumber = this.phoneNumber || '';
          
          let fullNumber = '';
          if (rawNumber.trim()) {
            if (rawNumber.startsWith('+')) {
              fullNumber = rawNumber;
            } else {
              fullNumber = `+${countryData.dialCode}${rawNumber}`;
            }
          }
          
          const isValid = this.validatePhoneNumber(rawNumber, countryData);
          
          this.$emit('update:modelValue', fullNumber);
          
          this.$emit('phone-change', {
            number: rawNumber,
            fullNumber: fullNumber,
            countryCode: countryData.dialCode,
            countryIso2: countryData.iso2,
            isValid: isValid
          });
        } catch (error) {
          console.warn('PhoneInputAdmin: Error emitting data', error);
        }
      }
    },
    validatePhoneNumber(number, countryData) {
      if (!number || !number.trim()) {
        return false;
      }
      
      const validationRules = {
        'ga': { min: 7, max: 8 }, // Gabon
        'cm': { min: 8, max: 9 }, // Cameroun  
        'ci': { min: 8, max: 10 }, // Côte d'Ivoire
        'fr': { min: 9, max: 10 }, // France
        'ca': { min: 10, max: 10 }, // Canada
        'cg': { min: 8, max: 9 }, // Congo
        'cf': { min: 8, max: 8 }, // République centrafricaine
        'td': { min: 8, max: 8 }, // Tchad
        'gq': { min: 9, max: 9 }, // Guinée équatoriale
      };
      
      const rule = validationRules[countryData.iso2] || { min: 7, max: 15 };
      const cleanNumber = number.replace(/[^\d]/g, '');
      
      return cleanNumber.length >= rule.min && cleanNumber.length <= rule.max;
    },
    getNumber() {
      if (this.iti && this.iti.getSelectedCountryData) {
        const countryData = this.iti.getSelectedCountryData();
        const rawNumber = this.phoneNumber || '';
        if (rawNumber.trim()) {
          return rawNumber.startsWith('+') ? rawNumber : `+${countryData.dialCode}${rawNumber}`;
        }
      }
      return '';
    },
    isValidNumber() {
      if (this.iti && this.iti.getSelectedCountryData) {
        const countryData = this.iti.getSelectedCountryData();
        return this.validatePhoneNumber(this.phoneNumber, countryData);
      }
      return false;
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

.phone-input-wrapper :deep(.admin-input),
.phone-input-wrapper input {
  padding-left: 95px !important;
  width: 100%;
  border: 1px solid var(--admin-border-medium);
  background-color: var(--admin-card-bg);
  color: var(--admin-text-primary);
  padding: 0.875rem 1rem;
  border-radius: 8px;
  font-size: 0.95rem;
  line-height: 1.5;
  transition: all 0.2s ease;
  min-width: 200px;
}

.phone-input-wrapper :deep(.iti__selected-dial-code) {
  margin-right: 10px;
}

.phone-input-wrapper :deep(.admin-input:focus),
.phone-input-wrapper input:focus {
  border-color: var(--admin-primary);
  box-shadow: 0 0 0 3px rgba(30, 58, 138, 0.1);
  outline: none;
}

.phone-input-wrapper :deep(.iti__country-list) {
  z-index: 9999;
}
</style>