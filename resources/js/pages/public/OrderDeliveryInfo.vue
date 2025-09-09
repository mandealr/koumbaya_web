<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Hero Section -->
    <div class="bg-gradient-to-r from-[#0099cc] to-[#0088bb] text-white">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="text-center">
          <h1 class="text-4xl font-bold mb-4">Informations sur la réception des commandes</h1>
          <p class="text-xl opacity-90">
            Tout ce que vous devez savoir sur la livraison de vos commandes
          </p>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        
        <!-- Introduction -->
        <div class="mb-12">
          <p class="text-lg text-gray-700 leading-relaxed">
            Chez Koumbaya, nous nous engageons à offrir à nos utilisateurs une expérience de livraison simple, transparente et avantageuse. Nous proposons la livraison standard gratuite sur la plupart des commandes, ainsi qu'une option de livraison express pour une réception accélérée.
          </p>
        </div>

        <!-- Modes de livraison et délais -->
        <div class="mb-12">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-[#0099cc] rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path>
              </svg>
            </div>
            <h2 class="text-3xl font-bold text-gray-900">Modes de livraison et délais</h2>
          </div>
          
          <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl p-8">
            <div class="overflow-x-auto">
              <table class="w-full bg-white rounded-lg shadow-sm">
                <thead class="bg-gray-50">
                  <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Méthode de livraison</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Coût</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Délai estimé</th>
                  </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                  <tr>
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-100 rounded-full flex items-center justify-center mr-3">
                          <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                          </svg>
                        </div>
                        <span class="font-medium text-gray-900">Livraison standard</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                        Gratuite sur la majorité des commandes
                      </span>
                    </td>
                    <td class="px-6 py-4 text-gray-700">4 à 7 jours ouvrés</td>
                  </tr>
                  <tr>
                    <td class="px-6 py-4">
                      <div class="flex items-center">
                        <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                          </svg>
                        </div>
                        <span class="font-medium text-gray-900">Livraison express</span>
                      </div>
                    </td>
                    <td class="px-6 py-4">
                      <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                        2500 FCFA
                      </span>
                    </td>
                    <td class="px-6 py-4 text-gray-700">1 jour ouvré</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-yellow-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.96-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                <div>
                  <p class="text-sm text-gray-700">
                    <strong>Remarque :</strong> Koumbaya se réserve le droit d'ajuster les seuils et frais de livraison en cas d'événements exceptionnels ou circonstances particulières. Les conditions applicables sont toujours clairement indiquées avant la validation de votre commande.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Options de livraison disponibles -->
        <div class="mb-12">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-green-500 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Options de livraison disponibles</h2>
          </div>
          
          <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-xl p-8">
            <p class="text-gray-700 mb-6">
              En fonction de votre localisation et du type d'articles commandés, vous pouvez choisir parmi les options suivantes :
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
              <div class="bg-white rounded-lg p-4 border-2 border-green-200">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4"></path>
                    </svg>
                  </div>
                  <span class="font-medium text-gray-900">Livraison standard</span>
                </div>
              </div>
              
              <div class="bg-white rounded-lg p-4 border-2 border-blue-200">
                <div class="flex items-center">
                  <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                  </div>
                  <span class="font-medium text-gray-900">Livraison express</span>
                </div>
              </div>
            </div>
            
            <div class="bg-orange-50 border-l-4 border-orange-400 p-4">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-orange-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-sm text-gray-700">
                  Veuillez noter que certains colis peuvent être confiés à des transporteurs locaux pour la dernière étape de la livraison. Dans ce cas, le suivi peut ne pas être disponible à chaque étape.
                </p>
              </div>
            </div>
          </div>
        </div>

        <!-- Adresse de livraison -->
        <div class="mb-12">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-purple-500 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Adresse de livraison</h2>
          </div>
          
          <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-xl p-8">
            <p class="text-gray-700 leading-relaxed">
              Merci de vérifier soigneusement l'exactitude de votre position de livraison (de préférence en utilisant les outils de géolocalisation et/ou mention de quartier, arrondissement annexé de tout complément d'information pertinent) avant de confirmer votre commande.
            </p>
          </div>
        </div>

        <!-- Délais et coûts de livraison -->
        <div class="mb-12">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-orange-500 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Délais et coûts de livraison</h2>
          </div>
          
          <div class="bg-gradient-to-r from-orange-50 to-orange-100 rounded-xl p-8">
            <p class="text-gray-700 mb-6">
              Une fois votre commande payée et confirmée, les délais et coûts de livraison seront clairement affichés sur votre page de confirmation. Vous recevrez également une notification récapitulatif précisant le délai de traitement estimé.
            </p>
            
            <div class="space-y-4">
              <div class="flex items-start">
                <div class="w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center mr-3 mt-0.5">
                  <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                  </svg>
                </div>
                <p class="text-gray-700">Le traitement d'une commande peut prendre 1 à 3 jours ouvrés.</p>
              </div>
              
              <div class="flex items-start">
                <div class="w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center mr-3 mt-0.5">
                  <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5"></path>
                  </svg>
                </div>
                <p class="text-gray-700">Vous recevrez une notification d'expédition avec un numéro de suivi dès que le colis est en route.</p>
              </div>
              
              <div class="flex items-start">
                <div class="w-6 h-6 bg-orange-200 rounded-full flex items-center justify-center mr-3 mt-0.5">
                  <svg class="w-3 h-3 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                  </svg>
                </div>
                <p class="text-gray-700">Vous pourrez consulter le délai estimé de livraison pour chaque colis dans l'historique de vos commandes.</p>
              </div>
            </div>
            
            <div class="mt-6 bg-blue-50 border-l-4 border-blue-400 p-4">
              <div class="flex items-start">
                <svg class="w-5 h-5 text-blue-500 mr-2 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div>
                  <p class="text-sm text-gray-700">
                    Bien que la plupart des commandes soient livrées dans les délais annoncés, la date de livraison effective peut être affectée par des facteurs externes (vols, météo, etc.). Pour suivre votre colis en temps réel, veuillez consulter les informations de suivi fournies.
                  </p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Problèmes de livraison -->
        <div class="mb-12">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-red-500 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.96-.833-2.732 0L3.732 16c-.77.833.192 2.5 1.732 2.5z"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Problèmes de livraison</h2>
          </div>
          
          <div class="bg-gradient-to-r from-red-50 to-red-100 rounded-xl p-8">
            <p class="text-gray-700 mb-4">Si votre colis :</p>
            
            <div class="space-y-3 mb-6">
              <div class="flex items-start">
                <div class="w-2 h-2 bg-red-500 rounded-full mr-3 mt-2"></div>
                <p class="text-gray-700">n'a pas été livré, ou</p>
              </div>
              <div class="flex items-start">
                <div class="w-2 h-2 bg-red-500 rounded-full mr-3 mt-2"></div>
                <p class="text-gray-700">le suivi indique une livraison effectuée mais que vous n'avez rien reçu,</p>
              </div>
            </div>
            
            <div class="bg-white rounded-lg p-6 border-2 border-red-200">
              <div class="flex items-center">
                <svg class="w-8 h-8 text-red-500 mr-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path>
                </svg>
                <div>
                  <p class="font-semibold text-gray-900 mb-1">Contactez notre service client</p>
                  <p class="text-gray-700 text-sm">immédiatement et dans un délai maximum de 90 jours suivant la date de commande.</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Collect - Retrait en personne -->
        <div class="mb-12">
          <div class="flex items-center mb-6">
            <div class="w-12 h-12 bg-indigo-500 rounded-lg flex items-center justify-center mr-4">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5"></path>
              </svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-900">Collect – Retrait en personne</h2>
          </div>
          
          <div class="bg-gradient-to-r from-indigo-50 to-indigo-100 rounded-xl p-8">
            <p class="text-gray-700 mb-6">
              Il se pourrait que Koumbaya opte pour que le koumbuyer vienne retirer votre commande directement dans un point de retrait défini par Koumbaya, si cette option est proposée pour les articles sélectionnés.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="bg-white rounded-lg p-6">
                <div class="flex items-center mb-4">
                  <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-5 5-5-5h5v-5"></path>
                  </svg>
                  <h3 class="font-semibold text-gray-900">Notification de retrait</h3>
                </div>
                <p class="text-gray-700 text-sm">
                  Lorsque votre commande est prête à être retirée, vous recevrez une notification par SMS ou e-mail avec toutes les instructions nécessaires.
                </p>
              </div>
              
              <div class="bg-white rounded-lg p-6">
                <div class="flex items-center mb-4">
                  <svg class="w-6 h-6 text-indigo-500 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V4a2 2 0 114 0v2m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"></path>
                  </svg>
                  <h3 class="font-semibold text-gray-900">Documents requis</h3>
                </div>
                <p class="text-gray-700 text-sm">
                  Veuillez-vous munir d'une pièce d'identité ainsi que de votre numéro de référence qui sera afféré pour effectuer le retrait.
                </p>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Call to Action -->
      <div class="mt-12 bg-white rounded-lg shadow-sm border border-gray-200 p-8 text-center">
        <h2 class="text-2xl font-bold text-gray-900 mb-4">Besoin d'aide ?</h2>
        <p class="text-gray-600 mb-6">
          Contactez notre service client pour toute question sur vos livraisons
        </p>
        <router-link
          to="/contact"
          class="inline-flex items-center justify-center px-8 py-3 bg-[#0099cc] text-white font-semibold rounded-lg hover:bg-[#0088bb] transition-colors"
        >
          Contacter le Support
        </router-link>
      </div>
    </div>
  </div>
</template>

<script setup>
// Page statique - pas de logique particulière nécessaire
</script>