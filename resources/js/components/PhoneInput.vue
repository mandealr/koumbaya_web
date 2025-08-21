<template>
  <div class="phone-input-wrapper">
    <input
      ref="phoneInput"
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
      const input = this.$refs.phoneInput;
      
      this.iti = intlTelInput(input, {
        preferredCountries: this.preferredCountries,
        initialCountry: this.initialCountry,
        separateDialCode: true,
        // Désactiver utilsScript pour éviter les problèmes de chargement
        // utilsScript: 'https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.8/js/utils.js',
        customPlaceholder: (selectedCountryPlaceholder, selectedCountryData) => {
          return selectedCountryPlaceholder;
        }
      });

      // Gérer l'événement input manuellement pour éviter les conflits avec v-model
      input.addEventListener('input', (e) => {
        this.phoneNumber = e.target.value;
        this.emitAllData();
      });

      input.addEventListener('countrychange', () => {
        this.emitAllData();
      });
      
      // Attendre que intl-tel-input soit complètement initialisé
      setTimeout(() => {
        // Définir la valeur initiale si elle existe
        if (this.modelValue) {
          this.iti.setNumber(this.modelValue);
        }
        
        // Émettre l'état initial même si le champ est vide
        // pour initialiser phoneValid dans le formulaire parent
        this.emitAllData();
      }, 100);
    },
    emitAllData() {
      if (this.iti && this.iti.getSelectedCountryData) {
        try {
          const countryData = this.iti.getSelectedCountryData();
          const rawNumber = this.phoneNumber || '';
          
          // Construire manuellement le numéro complet
          let fullNumber = '';
          if (rawNumber.trim()) {
            // Si le numéro ne commence pas par +, ajouter l'indicatif pays
            if (rawNumber.startsWith('+')) {
              fullNumber = rawNumber;
            } else {
              fullNumber = `+${countryData.dialCode}${rawNumber}`;
            }
          }
          
          // Validation manuelle basique
          const isValid = this.validatePhoneNumber(rawNumber, countryData);
          
          // Émettre la valeur pour le v-model
          this.$emit('update:modelValue', fullNumber);
          
          // Debug
          console.log('PhoneInput emitting:', {
            number: rawNumber,
            fullNumber: fullNumber,
            countryCode: countryData.dialCode,
            countryIso2: countryData.iso2,
            isValid: isValid
          });

          // Émettre les données détaillées
          this.$emit('phone-change', {
            number: rawNumber,
            fullNumber: fullNumber,
            countryCode: countryData.dialCode,
            countryIso2: countryData.iso2,
            isValid: isValid
          });
        } catch (error) {
          console.warn('PhoneInput: Error emitting data', error);
        }
      }
    },
    emitPhoneData() {
      this.emitAllData();
    },
    validatePhoneNumber(number, countryData) {
      if (!number || !number.trim()) {
        return false;
      }
      
      // Règles de validation par pays (basiques)
      const validationRules = {
        'ga': { min: 7, max: 8 }, // Gabon (07, 06, 05, etc.)
        'cm': { min: 8, max: 9 }, // Cameroun  
        'ci': { min: 8, max: 10 }, // Côte d'Ivoire
        'fr': { min: 9, max: 10 }, // France
        'ca': { min: 10, max: 10 }, // Canada
        'cg': { min: 8, max: 9 }, // Congo
        'cf': { min: 8, max: 8 }, // République centrafricaine
        'td': { min: 8, max: 8 }, // Tchad
        'gq': { min: 9, max: 9 }, // Guinée équatoriale
        'bf': { min: 8, max: 8 }, // Burkina Faso
        'bj': { min: 8, max: 8 }, // Bénin
        'tg': { min: 8, max: 8 }, // Togo
      };
      
      const rule = validationRules[countryData.iso2] || { min: 7, max: 15 };
      const cleanNumber = number.replace(/[^\d]/g, ''); // Enlever tout sauf les chiffres
      
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
    },
    setNumber(number) {
      if (this.iti) {
        this.phoneNumber = number;
        this.emitAllData();
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

.phone-input-wrapper :deep(.form-control),
.phone-input-wrapper input {
  padding-left: 95px !important;
  width: 100%;
  height: 45px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  font-size: 14px;
  background-color: #fff;
  color: #111827;
}

.phone-input-wrapper :deep(.iti__selected-dial-code) {
  margin-right: 10px;
}

.phone-input-wrapper :deep(.form-control:focus),
.phone-input-wrapper input:focus {
  border-color: #0099cc;
  box-shadow: 0 0 0 0.2rem rgba(0, 153, 204, 0.25);
  outline: none;
}

.phone-input-wrapper :deep(.iti__country-list) {
  z-index: 9999;
}
</style>