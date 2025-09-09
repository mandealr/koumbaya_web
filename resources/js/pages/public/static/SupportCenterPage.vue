<template>
  <StaticPageLayout
    title="Centre d'assistance"
    description="Trouvez rapidement des réponses à toutes vos questions sur Koumbaya"
    meta-description="Centre d'assistance Koumbaya - FAQ, guides et support client pour une expérience optimale"
  >
    <div class="space-y-12">
      <!-- Introduction -->
      <div class="text-center space-y-6">
        <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
          </svg>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">
            Comment pouvons-nous vous aider ?
          </h2>
          <p class="text-xl text-gray-700 leading-relaxed">
            Notre équipe support est là pour vous accompagner. Trouvez des réponses instantanées dans notre <strong class="text-blue-600">base de connaissances</strong> ou contactez-nous directement.
          </p>
        </div>
      </div>

      <!-- Recherche rapide -->
      <div class="bg-gradient-to-br from-blue-50 to-indigo-50 rounded-2xl p-8">
        <div class="max-w-2xl mx-auto">
          <h3 class="text-2xl font-bold text-gray-900 text-center mb-6">Recherche rapide</h3>
          <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
            </div>
            <input 
              type="text" 
              class="block w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent text-lg"
              placeholder="Tapez votre question ici... (ex: comment participer à une tombola)"
            />
            <div class="absolute inset-y-0 right-0 pr-4 flex items-center">
              <button class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg font-semibold transition-colors">
                Rechercher
              </button>
            </div>
          </div>
        </div>
      </div>

      <!-- Questions fréquentes -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">❓</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Questions fréquemment posées</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Compte et inscription -->
          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <div class="text-xl">👤</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Compte et inscription</h3>
            </div>
            <div class="space-y-3">
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Comment créer un compte Koumbaya ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Cliquez sur "S'inscrire", renseignez vos informations (email, téléphone, mot de passe) et validez votre compte via le SMS/email reçu.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  J'ai oublié mon mot de passe, que faire ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Cliquez sur "Mot de passe oublié" sur la page de connexion. Vous recevrez un lien de réinitialisation par email.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Comment modifier mes informations personnelles ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Allez dans votre profil > Paramètres pour modifier vos informations personnelles, mot de passe et préférences.
                </p>
              </details>
            </div>
          </div>

          <!-- Tombolas et tirages -->
          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center">
                <div class="text-xl">🎲</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Tombolas et tirages</h3>
            </div>
            <div class="space-y-3">
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Comment participer à une tombola ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Choisissez le produit qui vous intéresse, achetez vos tickets avec vos Gombos, et attendez le tirage !
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Quand ont lieu les tirages ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Les tirages se déroulent automatiquement à la date indiquée sur chaque produit. Vous êtes notifié du résultat.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Comment savoir si j'ai gagné ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Vous recevez une notification dans l'application et par email. Consultez aussi la section "Mes tickets" de votre profil.
                </p>
              </details>
            </div>
          </div>

          <!-- Gombos -->
          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-orange-600 rounded-full flex items-center justify-center">
                <div class="text-xl">💰</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Gombos et paiements</h3>
            </div>
            <div class="space-y-3">
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Qu'est-ce que les Gombos ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Les Gombos sont notre monnaie virtuelle qui vous permet d'acheter des tickets de tombola et de participer aux tirages.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Comment obtenir des Gombos ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Gagnez des Gombos via les missions quotidiennes, le parrainage, les achats ou achetez-en directement dans l'application.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Quels moyens de paiement acceptez-vous ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Nous acceptons les cartes bancaires, Mobile Money (Orange Money, MTN Money, Moov Money) et les virements.
                </p>
              </details>
            </div>
          </div>

          <!-- Livraison -->
          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                <div class="text-xl">📦</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Livraison et récupération</h3>
            </div>
            <div class="space-y-3">
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Comment récupérer mon prix ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Après avoir gagné, vous recevez un message avec les instructions. Vous pouvez choisir la livraison ou retrait en magasin.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  Combien de temps pour recevoir mon prix ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Les délais varient selon le produit et votre localisation. Généralement entre 3-7 jours ouvrables.
                </p>
              </details>
              <details class="group">
                <summary class="cursor-pointer text-gray-700 hover:text-blue-600 transition-colors font-medium">
                  La livraison est-elle gratuite ?
                </summary>
                <p class="text-gray-600 mt-2 pl-4">
                  Oui ! La livraison de vos prix est entièrement gratuite dans toutes nos zones de couverture.
                </p>
              </details>
            </div>
          </div>
        </div>
      </div>

      <!-- Guides et tutoriels -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">📚</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Guides et tutoriels</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 hover:shadow-lg transition-all cursor-pointer group">
            <div class="w-16 h-16 bg-blue-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
              <div class="text-2xl">🚀</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Guide de démarrage</h3>
            <p class="text-gray-600">
              Tout ce que vous devez savoir pour bien commencer sur Koumbaya
            </p>
            <div class="mt-4 flex items-center text-blue-600 font-semibold group-hover:underline">
              <span>Lire le guide</span>
              <svg class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </div>

          <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 hover:shadow-lg transition-all cursor-pointer group">
            <div class="w-16 h-16 bg-green-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
              <div class="text-2xl">💡</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Maximiser vos chances</h3>
            <p class="text-gray-600">
              Astuces et stratégies pour optimiser votre expérience sur Koumbaya
            </p>
            <div class="mt-4 flex items-center text-green-600 font-semibold group-hover:underline">
              <span>Découvrir les astuces</span>
              <svg class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-6 hover:shadow-lg transition-all cursor-pointer group">
            <div class="w-16 h-16 bg-purple-600 rounded-xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
              <div class="text-2xl">🛡️</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Sécurité et confidentialité</h3>
            <p class="text-gray-600">
              Protégez votre compte et vos données personnelles efficacement
            </p>
            <div class="mt-4 flex items-center text-purple-600 font-semibold group-hover:underline">
              <span>Renforcer ma sécurité</span>
              <svg class="h-4 w-4 ml-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
              </svg>
            </div>
          </div>
        </div>
      </div>

      <!-- Contact support -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">💬</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Contacter notre support</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
          <!-- Chat en direct -->
          <div class="bg-white border-2 border-green-200 rounded-2xl p-6 text-center hover:border-green-300 hover:shadow-lg transition-all">
            <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">💬</div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Chat en direct</h3>
            <p class="text-gray-600 text-sm mb-4">
              Réponse immédiate<br/>
              Lun-Ven 8h-20h
            </p>
            <button class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-xl font-semibold transition-colors w-full">
              Démarrer le chat
            </button>
          </div>

          <!-- Email -->
          <div class="bg-white border-2 border-blue-200 rounded-2xl p-6 text-center hover:border-blue-300 hover:shadow-lg transition-all">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">📧</div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Email</h3>
            <p class="text-gray-600 text-sm mb-4">
              Réponse sous 24h<br/>
              support@koumbaya.com
            </p>
            <a 
              href="mailto:support@koumbaya.com"
              class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-xl font-semibold transition-colors w-full inline-block"
            >
              Envoyer un email
            </a>
          </div>

          <!-- WhatsApp -->
          <div class="bg-white border-2 border-green-200 rounded-2xl p-6 text-center hover:border-green-300 hover:shadow-lg transition-all">
            <div class="w-16 h-16 bg-green-500 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">📱</div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">WhatsApp</h3>
            <p class="text-gray-600 text-sm mb-4">
              Support rapide<br/>
              +225 XX XX XX XX
            </p>
            <button class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-xl font-semibold transition-colors w-full">
              Contacter
            </button>
          </div>

          <!-- Téléphone -->
          <div class="bg-white border-2 border-orange-200 rounded-2xl p-6 text-center hover:border-orange-300 hover:shadow-lg transition-all">
            <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">📞</div>
            </div>
            <h3 class="text-lg font-bold text-gray-900 mb-2">Téléphone</h3>
            <p class="text-gray-600 text-sm mb-4">
              Lun-Ven 8h-18h<br/>
              +225 XX XX XX XX
            </p>
            <button class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-2 rounded-xl font-semibold transition-colors w-full">
              Appeler
            </button>
          </div>
        </div>
      </div>

      <!-- Statut du service -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Statut du service</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
          <div class="bg-white rounded-xl p-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="text-green-600 font-semibold">Opérationnel</span>
            </div>
            <h3 class="font-bold text-gray-900">Plateforme web</h3>
            <p class="text-gray-600 text-sm">Tous les services fonctionnent normalement</p>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="text-green-600 font-semibold">Opérationnel</span>
            </div>
            <h3 class="font-bold text-gray-900">Application mobile</h3>
            <p class="text-gray-600 text-sm">Toutes les fonctionnalités disponibles</p>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="flex items-center justify-center gap-2 mb-2">
              <div class="w-3 h-3 bg-green-500 rounded-full"></div>
              <span class="text-green-600 font-semibold">Opérationnel</span>
            </div>
            <h3 class="font-bold text-gray-900">Système de paiement</h3>
            <p class="text-gray-600 text-sm">Transactions sécurisées et rapides</p>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="bg-gradient-to-r from-blue-600 to-indigo-600 rounded-2xl p-12 text-white text-center">
        <div class="max-w-3xl mx-auto space-y-6">
          <div class="text-6xl mb-6">🎯</div>
          <h2 class="text-3xl md:text-4xl font-bold">Vous n'avez pas trouvé votre réponse ?</h2>
          <p class="text-xl text-blue-100 leading-relaxed">
            Notre équipe support expérimentée est là pour vous aider. N'hésitez pas à nous contacter !
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-6">
            <button class="inline-flex items-center gap-3 bg-white text-blue-600 px-8 py-4 rounded-xl font-bold hover:bg-blue-50 transition-all duration-200 hover:scale-[1.05] shadow-lg text-lg">
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
              </svg>
              Démarrer une conversation
            </button>
            
            <a
              href="mailto:support@koumbaya.com"
              class="inline-flex items-center gap-2 text-blue-100 hover:text-white font-semibold transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              Ou envoyez-nous un email
            </a>
          </div>
        </div>
      </div>
    </div>
  </StaticPageLayout>
</template>

<script setup>
import StaticPageLayout from './StaticPageLayout.vue'
</script>