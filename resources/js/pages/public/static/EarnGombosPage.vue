<template>
  <StaticPageLayout
    title="Gagnez des Gombos"
    description="Découvrez comment gagner des Gombos gratuitement et participez à plus de tombolas"
    meta-description="Gagnez des Gombos gratuitement et participez à plus de tombolas sur Koumbaya"
  >
    <div class="space-y-12">
      <!-- Introduction -->
      <div class="text-center space-y-6">
        <div class="w-16 h-16 bg-gradient-to-br from-yellow-500 to-orange-600 rounded-full flex items-center justify-center mx-auto">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
          </svg>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">
            Gagnez des Gombos gratuitement !
          </h2>
          <p class="text-xl text-gray-700 leading-relaxed">
            Les <strong class="text-yellow-600">Gombos</strong> sont notre monnaie virtuelle qui vous permet de participer aux tombolas Koumbaya. Plus vous en avez, plus vous pouvez tenter votre chance de remporter des articles incroyables !
          </p>
        </div>
      </div>

      <!-- Qu'est-ce qu'un Gombo -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">💰</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Qu'est-ce qu'un Gombo ?</h2>
        </div>

        <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-8">
          <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <div class="text-center">
              <div class="w-20 h-20 bg-yellow-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <span class="text-3xl font-bold text-white">G</span>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Monnaie virtuelle</h3>
              <p class="text-gray-600">
                Les Gombos sont notre système de points qui vous donne accès aux tombolas exclusives.
              </p>
            </div>
            <div class="text-center">
              <div class="w-20 h-20 bg-orange-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <div class="text-3xl">🎫</div>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Participation aux tombolas</h3>
              <p class="text-gray-600">
                Utilisez vos Gombos pour acheter des tickets et participer aux tirages de produits.
              </p>
            </div>
            <div class="text-center">
              <div class="w-20 h-20 bg-red-500 rounded-full flex items-center justify-center mx-auto mb-4">
                <div class="text-3xl">🎁</div>
              </div>
              <h3 class="text-xl font-semibold text-gray-900 mb-2">Récompenses exclusives</h3>
              <p class="text-gray-600">
                Accédez à des produits premium et des offres spéciales réservées aux détenteurs de Gombos.
              </p>
            </div>
          </div>
        </div>
      </div>

      <!-- Comment gagner des Gombos -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">🚀</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Comment gagner des Gombos ?</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <!-- Missions quotidiennes -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-8 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">📅</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Missions quotidiennes</h3>
              <span class="bg-blue-100 text-blue-800 text-sm font-semibold px-3 py-1 rounded-full">+5 à +50 Gombos</span>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Connexion quotidienne (+5 Gombos)</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Visiter 3 produits différents (+10 Gombos)</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Partager un produit sur les réseaux (+15 Gombos)</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Compléter votre profil (+20 Gombos)</span>
              </li>
            </ul>
          </div>

          <!-- Parrainage -->
          <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-8 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-purple-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">👥</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Parrainage</h3>
              <span class="bg-purple-100 text-purple-800 text-sm font-semibold px-3 py-1 rounded-full">+100 Gombos</span>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Invitez vos amis avec votre code de parrainage</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>100 Gombos pour chaque ami qui s'inscrit</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Bonus de 50 Gombos pour votre ami aussi !</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Pas de limite sur le nombre de parrainages</span>
              </li>
            </ul>
          </div>

          <!-- Événements spéciaux -->
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-8 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🎉</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Événements spéciaux</h3>
              <span class="bg-green-100 text-green-800 text-sm font-semibold px-3 py-1 rounded-full">Variable</span>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Concours mensuels sur les réseaux sociaux</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Double Gombos pendant les fêtes</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Événements surprise de la communauté</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Récompenses pour les utilisateurs actifs</span>
              </li>
            </ul>
          </div>

          <!-- Achats et engagement -->
          <div class="bg-gradient-to-br from-orange-50 to-red-50 border border-orange-200 rounded-2xl p-8 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-orange-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🛍️</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Achats & Engagement</h3>
              <span class="bg-orange-100 text-orange-800 text-sm font-semibold px-3 py-1 rounded-full">+1% du montant</span>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>1% en Gombos pour chaque achat</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Bonus fidélité pour les achats récurrents</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Écrire des avis produits (+25 Gombos)</span>
              </li>
              <li class="flex items-center gap-3">
                <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Participer aux sondages communauté</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Niveaux VIP -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center">
            <div class="text-2xl">👑</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Niveaux VIP Koumbaya</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <!-- Niveau Bronze -->
          <div class="bg-gradient-to-b from-orange-100 to-orange-200 border-2 border-orange-300 rounded-2xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-orange-400 to-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">🥉</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Bronze</h3>
            <p class="text-sm text-gray-600 mb-4">0 - 999 Gombos</p>
            <ul class="text-xs text-gray-700 space-y-1">
              <li>• Missions quotidiennes standard</li>
              <li>• Accès aux tombolas générales</li>
              <li>• Support client prioritaire</li>
            </ul>
          </div>

          <!-- Niveau Argent -->
          <div class="bg-gradient-to-b from-gray-100 to-gray-200 border-2 border-gray-400 rounded-2xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-gray-400 to-gray-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">🥈</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Argent</h3>
            <p class="text-sm text-gray-600 mb-4">1000 - 4999 Gombos</p>
            <ul class="text-xs text-gray-700 space-y-1">
              <li>• +25% bonus sur les missions</li>
              <li>• Tombolas exclusives argent</li>
              <li>• Accès anticipé aux nouveautés</li>
            </ul>
          </div>

          <!-- Niveau Or -->
          <div class="bg-gradient-to-b from-yellow-100 to-yellow-200 border-2 border-yellow-400 rounded-2xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-yellow-400 to-yellow-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">🥇</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Or</h3>
            <p class="text-sm text-gray-600 mb-4">5000 - 9999 Gombos</p>
            <ul class="text-xs text-gray-700 space-y-1">
              <li>• +50% bonus sur les missions</li>
              <li>• Tombolas premium exclusives</li>
              <li>• Manager de compte dédié</li>
            </ul>
          </div>

          <!-- Niveau Diamant -->
          <div class="bg-gradient-to-b from-blue-100 to-purple-200 border-2 border-purple-400 rounded-2xl p-6 text-center">
            <div class="w-16 h-16 bg-gradient-to-r from-blue-400 to-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">💎</div>
            </div>
            <h3 class="text-xl font-bold text-gray-900 mb-2">Diamant</h3>
            <p class="text-sm text-gray-600 mb-4">10000+ Gombos</p>
            <ul class="text-xs text-gray-700 space-y-1">
              <li>• +100% bonus sur les missions</li>
              <li>• Tombolas VIP ultra-exclusives</li>
              <li>• Accès événements privés</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Conseils pour maximiser vos gains -->
      <div class="bg-gradient-to-br from-indigo-50 to-purple-50 rounded-2xl p-8">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center">
            <div class="text-2xl text-white">💡</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Conseils pour maximiser vos gains</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">✅ Bonnes pratiques :</h3>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Connectez-vous tous les jours pour les bonus de connexion</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Complétez toutes vos missions quotidiennes</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Partagez Koumbaya avec vos amis pour les bonus de parrainage</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-green-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
                <span>Suivez nos réseaux sociaux pour les événements spéciaux</span>
              </li>
            </ul>
          </div>
          <div>
            <h3 class="text-xl font-semibold text-gray-900 mb-4">⚠️ Important à retenir :</h3>
            <ul class="space-y-3 text-gray-700">
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-yellow-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Les Gombos n'ont pas de valeur monétaire</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-yellow-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Les Gombos expirent après 1 an d'inactivité</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-yellow-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Utilisation interdite de bots ou méthodes automatisées</span>
              </li>
              <li class="flex items-start gap-3">
                <svg class="h-5 w-5 text-yellow-600 mt-1" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                </svg>
                <span>Les conditions peuvent évoluer selon nos conditions d'utilisation</span>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="bg-gradient-to-r from-yellow-600 to-orange-600 rounded-2xl p-12 text-white text-center">
        <div class="max-w-3xl mx-auto space-y-6">
          <div class="text-6xl mb-6">💰</div>
          <h2 class="text-3xl md:text-4xl font-bold">Commencez à collecter vos Gombos !</h2>
          <p class="text-xl text-yellow-100 leading-relaxed">
            Plus vous êtes actif sur Koumbaya, plus vous gagnez de Gombos. 
            Connectez-vous dès maintenant et commencez votre collection !
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-6">
            <a
              href="/login"
              class="inline-flex items-center gap-3 bg-white text-orange-600 px-8 py-4 rounded-xl font-bold hover:bg-yellow-50 transition-all duration-200 hover:scale-[1.05] shadow-lg text-lg"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15M12 9l-3 3m0 0 3 3m-3-3h12.75" />
              </svg>
              Me connecter
            </a>
            
            <a
              href="/products"
              class="inline-flex items-center gap-2 text-yellow-100 hover:text-white font-semibold transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
              </svg>
              Découvrir les tombolas
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