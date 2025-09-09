<template>
  <StaticPageLayout
    title="Plan du site"
    description="Explorez facilement toutes les pages et fonctionnalités de Koumbaya"
    meta-description="Plan du site Koumbaya - Navigation complète de toutes les pages et sections de notre plateforme"
  >
    <div class="space-y-12">
      <!-- Introduction -->
      <div class="text-center space-y-6">
        <div class="w-16 h-16 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center mx-auto">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
          </svg>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">
            Naviguer sur Koumbaya
          </h2>
          <p class="text-xl text-gray-700 leading-relaxed">
            Découvrez toutes les <strong class="text-indigo-600">pages et fonctionnalités</strong> disponibles sur notre plateforme. Trouvez rapidement ce que vous recherchez grâce à ce plan du site complet.
          </p>
        </div>
      </div>

      <!-- Pages principales -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">🏠</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Pages principales</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Accueil -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center">
                <div class="text-xl">🏠</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Accueil</h3>
            </div>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/" class="hover:text-blue-600 transition-colors">• Page d'accueil</a></li>
              <li><a href="/how-it-works" class="hover:text-blue-600 transition-colors">• Comment ça marche</a></li>
              <li><a href="/products" class="hover:text-blue-600 transition-colors">• Catalogue produits</a></li>
              <li><a href="/results" class="hover:text-blue-600 transition-colors">• Résultats des tirages</a></li>
              <li><a href="/history" class="hover:text-blue-600 transition-colors">• Historique des gagnants</a></li>
            </ul>
          </div>

          <!-- Compte utilisateur -->
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center">
                <div class="text-xl">👤</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Compte utilisateur</h3>
            </div>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/login" class="hover:text-green-600 transition-colors">• Connexion</a></li>
              <li><a href="/register" class="hover:text-green-600 transition-colors">• Inscription</a></li>
              <li><a href="/forgot-password" class="hover:text-green-600 transition-colors">• Mot de passe oublié</a></li>
              <li><a href="/verify-email" class="hover:text-green-600 transition-colors">• Vérification email</a></li>
              <li><a href="/customer" class="hover:text-green-600 transition-colors">• Tableau de bord</a></li>
            </ul>
          </div>

          <!-- Support et aide -->
          <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                <div class="text-xl">💬</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Support et aide</h3>
            </div>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/support-center" class="hover:text-purple-600 transition-colors">• Centre d'assistance</a></li>
              <li><a href="/contact" class="hover:text-purple-600 transition-colors">• Nous contacter</a></li>
              <li><a href="/report-suspicious" class="hover:text-purple-600 transition-colors">• Signaler une activité suspecte</a></li>
              <li><a href="/security-center" class="hover:text-purple-600 transition-colors">• Centre de sécurité</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Espaces utilisateurs -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">👥</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Espaces utilisateurs</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
          <!-- Espace client -->
          <div class="bg-white border-2 border-green-200 rounded-2xl p-8 hover:border-green-300 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🛍️</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Espace client</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/customer" class="hover:text-green-600 transition-colors font-medium">• Tableau de bord</a></li>
              <li><a href="/customer/products" class="hover:text-green-600 transition-colors">• Produits disponibles</a></li>
              <li><a href="/customer/tickets" class="hover:text-green-600 transition-colors">• Mes tickets</a></li>
              <li><a href="/customer/profile" class="hover:text-green-600 transition-colors">• Mon profil</a></li>
              <li><a href="/customer/orders" class="hover:text-green-600 transition-colors">• Mes commandes</a></li>
              <li><a href="/customer/payments" class="hover:text-green-600 transition-colors">• Mes paiements</a></li>
              <li><a href="/customer/refunds" class="hover:text-green-600 transition-colors">• Mes remboursements</a></li>
              <li><a href="/customer/notifications" class="hover:text-green-600 transition-colors">• Notifications</a></li>
            </ul>
          </div>

          <!-- Espace marchand -->
          <div class="bg-white border-2 border-blue-200 rounded-2xl p-8 hover:border-blue-300 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">🏪</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Espace marchand</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/merchant" class="hover:text-blue-600 transition-colors font-medium">• Tableau de bord</a></li>
              <li><a href="/merchant/products" class="hover:text-blue-600 transition-colors">• Gérer les produits</a></li>
              <li><a href="/merchant/lotteries" class="hover:text-blue-600 transition-colors">• Mes tombolas</a></li>
              <li><a href="/merchant/orders" class="hover:text-blue-600 transition-colors">• Gestion des commandes</a></li>
              <li><a href="/merchant/analytics" class="hover:text-blue-600 transition-colors">• Statistiques</a></li>
              <li><a href="/merchant/settings" class="hover:text-blue-600 transition-colors">• Paramètres</a></li>
              <li><a href="/merchant/profile" class="hover:text-blue-600 transition-colors">• Mon profil</a></li>
              <li><a href="/merchant/notifications" class="hover:text-blue-600 transition-colors">• Notifications</a></li>
            </ul>
          </div>

          <!-- Espace administrateur -->
          <div class="bg-white border-2 border-red-200 rounded-2xl p-8 hover:border-red-300 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-red-600 rounded-full flex items-center justify-center">
                <div class="text-2xl">⚙️</div>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Espace administrateur</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/admin" class="hover:text-red-600 transition-colors font-medium">• Tableau de bord admin</a></li>
              <li><a href="/admin/users" class="hover:text-red-600 transition-colors">• Gestion utilisateurs</a></li>
              <li><a href="/admin/products" class="hover:text-red-600 transition-colors">• Gestion produits</a></li>
              <li><a href="/admin/lotteries" class="hover:text-red-600 transition-colors">• Gestion tombolas</a></li>
              <li><a href="/admin/orders" class="hover:text-red-600 transition-colors">• Gestion commandes</a></li>
              <li><a href="/admin/payments" class="hover:text-red-600 transition-colors">• Gestion paiements</a></li>
              <li><a href="/admin/refunds" class="hover:text-red-600 transition-colors">• Gestion remboursements</a></li>
              <li><a href="/admin/settings" class="hover:text-red-600 transition-colors">• Paramètres système</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Pages légales et informatives -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-gray-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">📜</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Pages légales et informatives</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Pages légales</h3>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/terms" class="hover:text-blue-600 transition-colors">• Conditions d'utilisation</a></li>
              <li><a href="/privacy" class="hover:text-blue-600 transition-colors">• Politique de confidentialité</a></li>
              <li><a href="/legal" class="hover:text-blue-600 transition-colors">• Mentions légales</a></li>
              <li><a href="/cookies" class="hover:text-blue-600 transition-colors">• Politique des cookies</a></li>
              <li><a href="/intellectual-property" class="hover:text-blue-600 transition-colors">• Propriété intellectuelle</a></li>
              <li><a href="/lottery-participation" class="hover:text-blue-600 transition-colors">• Règles de participation</a></li>
            </ul>
          </div>

          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Pages informatives</h3>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/about" class="hover:text-blue-600 transition-colors">• À propos de Koumbaya</a></li>
              <li><a href="/careers" class="hover:text-blue-600 transition-colors">• Carrières</a></li>
              <li><a href="/media-press" class="hover:text-blue-600 transition-colors">• Médias et presse</a></li>
              <li><a href="/order-delivery" class="hover:text-blue-600 transition-colors">• Livraison et expédition</a></li>
              <li><a href="/earn-gombos" class="hover:text-blue-600 transition-colors">• Gagner des Gombos</a></li>
              <li><a href="/peace-on-koumbaya" class="hover:text-blue-600 transition-colors">• Paix sur Koumbaya</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Pages partenaires -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">🤝</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Pages partenaires</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-10 h-10 bg-yellow-600 rounded-full flex items-center justify-center">
                <div class="text-xl">💼</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Programmes de partenariat</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/affiliates" class="hover:text-yellow-600 transition-colors font-medium">• Programme d'affiliation</a></li>
              <li><a href="/sell-on-koumbaya" class="hover:text-yellow-600 transition-colors">• Vendre sur Koumbaya</a></li>
              <li class="text-gray-500">• Programme API développeurs (bientôt)</li>
              <li class="text-gray-500">• Partenariats d'entreprise (bientôt)</li>
            </ul>
          </div>

          <div class="bg-gradient-to-br from-purple-50 to-indigo-50 border border-purple-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-10 h-10 bg-purple-600 rounded-full flex items-center justify-center">
                <div class="text-xl">🔧</div>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Outils et ressources</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/verify" class="hover:text-purple-600 transition-colors">• Vérifier un gagnant</a></li>
              <li class="text-gray-500">• Kit média (bientôt)</li>
              <li class="text-gray-500">• Documentation API (bientôt)</li>
              <li class="text-gray-500">• Centre de ressources (bientôt)</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Fonctionnalités spéciales -->
      <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-2xl p-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center">
            <div class="text-2xl text-white">⚡</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Fonctionnalités spéciales</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">💳</div>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Paiements</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Méthodes de paiement</li>
              <li>• Confirmation de paiement</li>
              <li>• Historique des transactions</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">🔔</div>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Notifications</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Notifications en temps réel</li>
              <li>• Alertes par email</li>
              <li>• Préférences de notifications</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-purple-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">📱</div>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Applications mobiles</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Application iOS (bientôt)</li>
              <li>• Application Android (bientôt)</li>
              <li>• Version PWA disponible</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-orange-600 rounded-full flex items-center justify-center mx-auto mb-4">
              <div class="text-2xl">🌐</div>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Multilingue</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Français (disponible)</li>
              <li>• Anglais (bientôt)</li>
              <li>• Autres langues (bientôt)</li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Recherche avancée -->
      <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-200 rounded-2xl p-8">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-indigo-600 rounded-full flex items-center justify-center">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Vous cherchez quelque chose de spécifique ?</h2>
        </div>

        <div class="max-w-2xl mx-auto">
          <div class="relative mb-8">
            <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
              <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
              </svg>
            </div>
            <input 
              type="text" 
              class="block w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-indigo-500 focus:border-transparent text-lg"
              placeholder="Recherchez une page, une fonctionnalité... (ex: tombola, paiement, profil)"
            />
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <div class="text-2xl mb-2">🎲</div>
              <p class="text-sm font-medium text-gray-700">Tombolas</p>
            </div>
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <div class="text-2xl mb-2">💰</div>
              <p class="text-sm font-medium text-gray-700">Gombos</p>
            </div>
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <div class="text-2xl mb-2">🏆</div>
              <p class="text-sm font-medium text-gray-700">Gagnants</p>
            </div>
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <div class="text-2xl mb-2">🛡️</div>
              <p class="text-sm font-medium text-gray-700">Sécurité</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="bg-gradient-to-r from-indigo-600 to-purple-600 rounded-2xl p-12 text-white text-center">
        <div class="max-w-3xl mx-auto space-y-6">
          <div class="text-6xl mb-6">🗺️</div>
          <h2 class="text-3xl md:text-4xl font-bold">Besoin d'aide pour naviguer ?</h2>
          <p class="text-xl text-indigo-100 leading-relaxed">
            Si vous ne trouvez pas ce que vous cherchez dans ce plan du site, notre équipe support est là pour vous guider.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-6">
            <a
              href="/support-center"
              class="inline-flex items-center gap-3 bg-white text-indigo-600 px-8 py-4 rounded-xl font-bold hover:bg-indigo-50 transition-all duration-200 hover:scale-[1.05] shadow-lg text-lg"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
              </svg>
              Centre d'assistance
            </a>
            
            <a
              href="/contact"
              class="inline-flex items-center gap-2 text-indigo-100 hover:text-white font-semibold transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              Ou contactez-nous
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