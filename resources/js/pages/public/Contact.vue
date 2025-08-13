<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#0099cc] via-blue-500 to-cyan-600 text-white py-20">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          Contactez-nous
        </h1>
        <p class="text-xl md:text-2xl text-blue-100 leading-relaxed">
          Notre équipe est là pour répondre à toutes vos questions. 
          N'hésitez pas à nous contacter !
        </p>
      </div>
    </section>

    <!-- Contact Options -->
    <section class="py-20">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
          <!-- Phone -->
          <div class="text-center group">
            <div class="w-20 h-20 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-[#0099cc] transition-colors">
              <PhoneIcon class="h-10 w-10 text-[#0099cc] group-hover:text-white transition-colors" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Téléphone</h3>
            <p class="text-gray-600 mb-4">Appelez-nous directement</p>
            <div class="space-y-2">
              <a href="tel:+24107123456" class="block text-[#0099cc] hover:text-[#0088bb] font-medium">
                +241 07 12 34 56
              </a>
              <a href="tel:+24106987654" class="block text-[#0099cc] hover:text-[#0088bb] font-medium">
                +241 06 98 76 54
              </a>
            </div>
            <p class="text-sm text-gray-500 mt-2">Lun - Ven : 8h - 20h</p>
          </div>

          <!-- Email -->
          <div class="text-center group">
            <div class="w-20 h-20 bg-blue-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-blue-500 transition-colors">
              <EnvelopeIcon class="h-10 w-10 text-blue-500 group-hover:text-white transition-colors" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">Email</h3>
            <p class="text-gray-600 mb-4">Écrivez-nous un message</p>
            <div class="space-y-2">
              <a href="mailto:contact@koumbaya.ga" class="block text-blue-600 hover:text-blue-700 font-medium">
                contact@koumbaya.ga
              </a>
              <a href="mailto:support@koumbaya.ga" class="block text-blue-600 hover:text-blue-700 font-medium">
                support@koumbaya.ga
              </a>
            </div>
            <p class="text-sm text-gray-500 mt-2">Réponse sous 24h</p>
          </div>

          <!-- WhatsApp -->
          <div class="text-center group">
            <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6 group-hover:bg-yellow-500 transition-colors">
              <ChatBubbleLeftEllipsisIcon class="h-10 w-10 text-yellow-500 group-hover:text-white transition-colors" />
            </div>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">WhatsApp</h3>
            <p class="text-gray-600 mb-4">Chat en temps réel</p>
            <a 
              href="https://wa.me/24107123456" 
              target="_blank"
              class="inline-block bg-[#0099cc] text-white px-6 py-3 rounded-xl font-medium hover:bg-[#0088bb] transition-colors"
            >
              Chatter maintenant
            </a>
            <p class="text-sm text-gray-500 mt-2">Disponible 24h/7j</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Contact Form & Office Info -->
    <section class="py-20 bg-gray-50">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-16">
          <!-- Contact Form -->
          <div class="bg-white rounded-2xl p-8 shadow-lg">
            <div class="mb-8">
              <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-4">
                Envoyez-nous un message
              </h2>
              <p class="text-gray-600">
                Remplissez le formulaire ci-dessous et nous vous répondrons dans les plus brefs délais.
              </p>
            </div>

            <form @submit.prevent="submitForm" class="space-y-6">
              <!-- Name & Email -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Nom complet *
                  </label>
                  <input
                    v-model="form.name"
                    type="text"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
                    placeholder="Votre nom"
                  />
                  <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name }}</div>
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Adresse email *
                  </label>
                  <input
                    v-model="form.email"
                    type="email"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
                    placeholder="votre@email.com"
                  />
                  <div v-if="errors.email" class="text-red-500 text-sm mt-1">{{ errors.email }}</div>
                </div>
              </div>

              <!-- Phone & Subject -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Numéro de téléphone
                  </label>
                  <input
                    v-model="form.phone"
                    type="tel"
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
                    placeholder="+241 XX XX XX XX"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Sujet *
                  </label>
                  <select
                    v-model="form.subject"
                    required
                    class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent transition-all"
                  >
                    <option value="">Choisir un sujet</option>
                    <option value="support">Support technique</option>
                    <option value="billing">Facturation</option>
                    <option value="product">Question sur un produit</option>
                    <option value="tombola">Question sur une tombola</option>
                    <option value="delivery">Livraison</option>
                    <option value="partnership">Partenariat</option>
                    <option value="other">Autre</option>
                  </select>
                  <div v-if="errors.subject" class="text-red-500 text-sm mt-1">{{ errors.subject }}</div>
                </div>
              </div>

              <!-- Message -->
              <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                  Message *
                </label>
                <textarea
                  v-model="form.message"
                  required
                  rows="6"
                  class="w-full px-4 py-3 border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all resize-none"
                  placeholder="Décrivez votre demande en détail..."
                ></textarea>
                <div v-if="errors.message" class="text-red-500 text-sm mt-1">{{ errors.message }}</div>
              </div>

              <!-- Agreement -->
              <div class="flex items-start gap-3">
                <input
                  v-model="form.agree"
                  type="checkbox"
                  id="agree"
                  required
                  class="mt-1 h-4 w-4 text-[#0099cc] border-gray-200 rounded focus:ring-[#0099cc]"
                />
                <label for="agree" class="text-sm text-gray-600">
                  J'accepte que mes données soient utilisées pour traiter ma demande selon notre
                  <router-link to="/privacy" class="text-[#0099cc] hover:text-[#0088bb]">politique de confidentialité</router-link>.
                </label>
              </div>

              <!-- Submit Button -->
              <button
                type="submit"
                :disabled="loading"
                class="w-full bg-[#0099cc] hover:bg-[#0088bb] disabled:opacity-50 text-white font-semibold py-4 rounded-xl transition-all duration-200 hover:scale-[1.02] shadow-lg hover:shadow-xl"
              >
                <span v-if="loading" class="flex items-center justify-center gap-2">
                  <div class="animate-spin rounded-full h-5 w-5 border-2 border-white border-t-transparent"></div>
                  Envoi en cours...
                </span>
                <span v-else>Envoyer le message</span>
              </button>
            </form>
          </div>

          <!-- Office Info & Map -->
          <div class="space-y-8">
            <!-- Office Info -->
            <div class="bg-white rounded-2xl p-8 shadow-lg">
              <h3 class="text-2xl font-bold text-gray-900 mb-6">Nos bureaux</h3>
              
              <div class="space-y-6">
                <!-- Douala Office -->
                <div class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-[#0099cc]/10 rounded-full flex items-center justify-center">
                      <MapPinIcon class="h-6 w-6 text-[#0099cc]" />
                    </div>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Libreville (Siège social)</h4>
                    <p class="text-gray-600 mb-2">
                      Boulevard Triomphal, Centre-ville<br />
                      Face à l'Hôtel Intercontinental<br />
                      BP 1234 Libreville, Gabon
                    </p>
                    <div class="text-sm text-gray-500">
                      <p>Lun - Ven : 8h00 - 18h00</p>
                      <p>Sam : 9h00 - 15h00</p>
                    </div>
                  </div>
                </div>

                <!-- Yaoundé Office -->
                <div class="flex gap-4">
                  <div class="flex-shrink-0">
                    <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
                      <MapPinIcon class="h-6 w-6 text-blue-500" />
                    </div>
                  </div>
                  <div>
                    <h4 class="font-semibold text-gray-900 mb-2">Port-Gentil</h4>
                    <p class="text-gray-600 mb-2">
                      Avenue Savorgnan de Brazza<br />
                      Centre Commercial Delta<br />
                      BP 5678 Port-Gentil, Gabon
                    </p>
                    <div class="text-sm text-gray-500">
                      <p>Lun - Ven : 8h30 - 17h30</p>
                      <p>Sam : 9h00 - 14h00</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- Quick Contact -->
            <div class="bg-gradient-to-br from-[#0099cc] to-cyan-600 rounded-2xl p-8 text-white">
              <h3 class="text-2xl font-bold mb-4">Besoin d'une réponse rapide ?</h3>
              <p class="text-blue-100 mb-6">
                Notre équipe de support est disponible pour vous aider immédiatement.
              </p>
              
              <div class="grid grid-cols-2 gap-4">
                <a
                  href="tel:+24107123456"
                  class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors"
                >
                  <PhoneIcon class="h-6 w-6" />
                  <div class="text-left">
                    <div class="font-medium">Appelez-nous</div>
                    <div class="text-sm text-blue-100">24h/7j</div>
                  </div>
                </a>
                
                <a
                  href="https://wa.me/24107123456"
                  target="_blank"
                  class="flex items-center gap-3 bg-white/10 backdrop-blur-sm rounded-xl p-4 hover:bg-white/20 transition-colors"
                >
                  <ChatBubbleLeftEllipsisIcon class="h-6 w-6" />
                  <div class="text-left">
                    <div class="font-medium">WhatsApp</div>
                    <div class="text-sm text-blue-100">Chat live</div>
                  </div>
                </a>
              </div>
            </div>

            <!-- FAQ Link -->
            <div class="bg-gray-100 rounded-2xl p-8">
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Consultez d'abord notre FAQ</h3>
              <p class="text-gray-600 mb-6">
                La plupart des questions trouvent leur réponse dans notre section FAQ.
              </p>
              <router-link
                to="/how-it-works"
                class="inline-flex items-center gap-2 bg-white text-gray-900 px-6 py-3 rounded-xl font-medium hover:bg-gray-50 transition-colors shadow-sm"
              >
                <QuestionMarkCircleIcon class="h-5 w-5" />
                Voir les questions fréquentes
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Success Modal -->
    <div v-if="showSuccessModal" class="fixed inset-0 z-50 overflow-y-auto">
      <div class="flex items-center justify-center min-h-screen px-4 text-center">
        <div class="fixed inset-0 bg-black bg-opacity-20 transition-opacity" @click="showSuccessModal = false"></div>
        
        <div class="inline-block w-full max-w-md p-6 my-8 overflow-hidden text-left align-middle bg-white shadow-xl rounded-2xl transform transition-all">
          <div class="text-center">
            <div class="w-16 h-16 bg-[#0099cc]/10 rounded-full flex items-center justify-center mx-auto mb-4">
              <CheckCircleIcon class="h-8 w-8 text-[#0099cc]" />
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Message envoyé !</h3>
            <p class="text-gray-600 mb-6">
              Merci pour votre message. Nous vous répondrons dans les plus brefs délais.
            </p>
            <button
              @click="showSuccessModal = false"
              class="bg-[#0099cc] hover:bg-[#0088bb] text-white px-6 py-3 rounded-xl font-medium transition-colors"
            >
              Parfait
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import {
  PhoneIcon,
  EnvelopeIcon,
  ChatBubbleLeftEllipsisIcon,
  MapPinIcon,
  QuestionMarkCircleIcon,
  CheckCircleIcon
} from '@heroicons/vue/24/outline'

const loading = ref(false)
const showSuccessModal = ref(false)

const form = reactive({
  name: '',
  email: '',
  phone: '',
  subject: '',
  message: '',
  agree: false
})

const errors = reactive({
  name: '',
  email: '',
  subject: '',
  message: ''
})

const validateForm = () => {
  let isValid = true
  
  // Reset errors
  Object.keys(errors).forEach(key => {
    errors[key] = ''
  })
  
  // Name validation
  if (!form.name.trim()) {
    errors.name = 'Le nom est requis'
    isValid = false
  } else if (form.name.trim().length < 2) {
    errors.name = 'Le nom doit contenir au moins 2 caractères'
    isValid = false
  }
  
  // Email validation
  if (!form.email.trim()) {
    errors.email = 'L\'email est requis'
    isValid = false
  } else if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(form.email)) {
    errors.email = 'Format d\'email invalide'
    isValid = false
  }
  
  // Subject validation
  if (!form.subject) {
    errors.subject = 'Veuillez sélectionner un sujet'
    isValid = false
  }
  
  // Message validation
  if (!form.message.trim()) {
    errors.message = 'Le message est requis'
    isValid = false
  } else if (form.message.trim().length < 10) {
    errors.message = 'Le message doit contenir au moins 10 caractères'
    isValid = false
  }
  
  return isValid
}

const submitForm = async () => {
  if (!validateForm()) {
    return
  }
  
  loading.value = true
  
  try {
    // Simulate API call
    await new Promise(resolve => setTimeout(resolve, 1500))
    
    // Reset form
    Object.keys(form).forEach(key => {
      if (key === 'agree') {
        form[key] = false
      } else {
        form[key] = ''
      }
    })
    
    showSuccessModal.value = true
    
    if (window.$toast) {
      window.$toast.success('Message envoyé avec succès !', 'Contact')
    }
  } catch (error) {
    if (window.$toast) {
      window.$toast.error('Erreur lors de l\'envoi du message', 'Erreur')
    }
  } finally {
    loading.value = false
  }
}
</script>