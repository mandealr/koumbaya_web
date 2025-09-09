<template>
  <StaticPageLayout
    title="Signaler une activité suspecte"
    description="Aidez-nous à maintenir un environnement sûr pour tous nos utilisateurs"
    meta-description="Signalez une activité suspecte sur Koumbaya et aidez-nous à maintenir la sécurité de notre plateforme"
  >
    <div class="space-y-12">
      <!-- Introduction -->
      <div class="text-center space-y-6">
        <div class="w-16 h-16 bg-gradient-to-br from-red-500 to-orange-600 rounded-full flex items-center justify-center mx-auto">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
          </svg>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <p class="text-xl text-gray-700 leading-relaxed">
            Chez Koumbaya, la <strong class="text-red-600">sécurité et la confiance</strong> de notre communauté sont nos priorités absolues. Si vous constatez une activité suspecte, n'hésitez pas à nous le signaler.
          </p>
        </div>
      </div>

      <!-- Quand signaler -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-red-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">🚨</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Quand signaler une activité suspecte ?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Fraudes et escroqueries -->
          <div class="bg-gradient-to-br from-red-50 to-pink-50 border border-red-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">⚠️</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Fraudes et escroqueries</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Demandes d'informations personnelles ou bancaires</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Offres trop belles pour être vraies</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Usurpation d'identité ou faux profils</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Tentatives de phishing ou d'hameçonnage</span>
              </li>
            </ul>
          </div>

          <!-- Comportements inappropriés -->
          <div class="bg-gradient-to-br from-orange-50 to-yellow-50 border border-orange-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🛑</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Comportements inappropriés</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-orange-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Harcèlement ou intimidation</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-orange-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Contenu inapproprié ou offensant</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-orange-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Spam ou messages répétés</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-orange-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Violation des conditions d'utilisation</span>
              </li>
            </ul>
          </div>

          <!-- Problèmes techniques -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🔧</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Problèmes techniques suspects</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Dysfonctionnements inhabituels de l'application</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Redirections vers des sites externes non autorisés</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Activité anormale sur votre compte</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-blue-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Tentatives de piratage ou d'intrusion</span>
              </li>
            </ul>
          </div>

          <!-- Produits et marchands -->
          <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🛍️</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Produits et marchands</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-purple-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Produits contrefaits ou illégaux</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-purple-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Descriptions de produits trompeuses</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-purple-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Marchands aux pratiques douteuses</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-purple-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <span>Tombolas manipulées ou frauduleuses</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Comment signaler -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">📧</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Comment signaler ?</h2>
        </div>

        <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-8">
          <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div>
              <h3 class="text-2xl font-semibold text-gray-900 mb-6">Informations à fournir :</h3>
              <ul class="space-y-4 text-gray-700">
                <li class="flex items-start gap-3">
                  <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-white text-sm font-bold">1</span>
                  </div>
                  <span><strong>Description détaillée</strong> de l'incident</span>
                </li>
                <li class="flex items-start gap-3">
                  <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-white text-sm font-bold">2</span>
                  </div>
                  <span><strong>Date et heure</strong> de l'incident</span>
                </li>
                <li class="flex items-start gap-3">
                  <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-white text-sm font-bold">3</span>
                  </div>
                  <span><strong>Captures d'écran</strong> ou preuves (si possible)</span>
                </li>
                <li class="flex items-start gap-3">
                  <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-white text-sm font-bold">4</span>
                  </div>
                  <span><strong>Identifiants</strong> des utilisateurs ou produits concernés</span>
                </li>
                <li class="flex items-start gap-3">
                  <div class="w-6 h-6 bg-green-600 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                    <span class="text-white text-sm font-bold">5</span>
                  </div>
                  <span><strong>Vos coordonnées</strong> pour un suivi éventuel</span>
                </li>
              </ul>
            </div>

            <div class="flex flex-col justify-center items-center text-center bg-white rounded-xl p-8">
              <div class="w-20 h-20 bg-green-600 rounded-full flex items-center justify-center mb-6">
                <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
              </div>
              
              <h3 class="text-xl font-bold text-gray-900 mb-4">Contactez notre équipe sécurité</h3>
              <a 
                href="mailto:security@koumbaya.com" 
                class="inline-flex items-center gap-3 text-xl font-semibold text-green-600 hover:text-green-700 transition-colors mb-2"
              >
                security@koumbaya.com
              </a>
              <p class="text-sm text-gray-600">
                Réponse sous 24h • Confidentialité garantie
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Processus de traitement -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">⚡</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Notre processus de traitement</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="text-center">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">1</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Réception</h3>
            <p class="text-gray-600">Votre signalement est immédiatement enregistré dans notre système sécurisé</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">2</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Analyse</h3>
            <p class="text-gray-600">Notre équipe sécurité examine le signalement sous 24h</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">3</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Investigation</h3>
            <p class="text-gray-600">Enquête approfondie et collecte d'éléments complémentaires</p>
          </div>
          
          <div class="text-center">
            <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <span class="text-2xl font-bold text-white">4</span>
            </div>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Action</h3>
            <p class="text-gray-600">Mesures correctives appliquées et suivi de l'affaire</p>
          </div>
        </div>
      </div>

      <!-- Conseils de prévention -->
      <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center">
            <div class="text-2xl text-white">💡</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Conseils de prévention</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">✅ Bonnes pratiques :</h3>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Vérifiez toujours l'identité des marchands</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Ne communiquez jamais vos informations personnelles</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Utilisez des mots de passe forts et uniques</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Restez vigilant face aux offres trop attractives</span>
              </li>
            </ul>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">⚠️ Signaux d'alerte :</h3>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Messages urgents demandant des informations</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Prix anormalement bas</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Pression pour agir rapidement</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-red-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Méthodes de paiement inhabituelles</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="bg-gradient-to-r from-red-600 to-orange-600 rounded-2xl p-12 text-white text-center">
        <div class="max-w-3xl mx-auto space-y-6">
          <div class="text-6xl mb-6">🚨</div>
          <h2 class="text-3xl md:text-4xl font-bold">Agissez maintenant</h2>
          <p class="text-xl text-red-100 leading-relaxed">
            Votre vigilance protège toute notre communauté. N'hésitez pas à signaler toute activité suspecte.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-6">
            <a
              href="mailto:security@koumbaya.com"
              class="inline-flex items-center gap-3 bg-white text-red-600 px-8 py-4 rounded-xl font-bold hover:bg-red-50 transition-all duration-200 hover:scale-[1.05] shadow-lg text-lg"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              Signaler maintenant
            </a>
            
            <a
              href="/security-center"
              class="inline-flex items-center gap-2 text-red-100 hover:text-white font-semibold transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
              </svg>
              Centre de sécurité
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