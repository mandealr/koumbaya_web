<template>
  <StaticPageLayout
    title="Plan du site"
    description="Explorez facilement toutes les pages et fonctionnalités de Koumbaya"
    meta-description="Plan du site Koumbaya - Navigation complète de toutes les pages et sections de notre plateforme"
  >
    <div class="space-y-12">
      <!-- Introduction -->
      <div class="text-center space-y-6">
        <div class="w-16 h-16 bg-gradient-to-br from-[#0099cc] to-[#006699] rounded-full flex items-center justify-center mx-auto">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
          </svg>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <h2 class="text-2xl md:text-3xl font-bold text-gray-900 mb-6">
            Naviguer sur Koumbaya
          </h2>
          <p class="text-xl text-gray-700 leading-relaxed">
            Découvrez toutes les <strong class="text-[#0099cc]">pages et fonctionnalités</strong> disponibles sur notre plateforme. Trouvez rapidement ce que vous recherchez grâce à ce plan du site complet.
          </p>
        </div>
      </div>

      <!-- Pages principales -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-[#0099cc]/10 rounded-full flex items-center justify-center">
            <svg class="h-6 w-6 text-[#0099cc]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Pages principales</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
          <!-- Accueil -->
          <div class="bg-gradient-to-br from-[#0099cc]/5 to-[#006699]/5 border border-[#0099cc]/20 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Accueil</h3>
            </div>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/" class="hover:text-[#0099cc] transition-colors">• Page d'accueil</a></li>
              <li><a href="/how-it-works" class="hover:text-[#0099cc] transition-colors">• Comment ça marche</a></li>
              <li><a href="/products" class="hover:text-[#0099cc] transition-colors">• Catalogue produits</a></li>
              <li><a href="/results" class="hover:text-[#0099cc] transition-colors">• Résultats des tirages</a></li>
              <li><a href="/history" class="hover:text-[#0099cc] transition-colors">• Historique des gagnants</a></li>
            </ul>
          </div>

          <!-- Compte utilisateur -->
          <div class="bg-gradient-to-br from-[#0099cc]/5 to-[#006699]/5 border border-[#0099cc]/20 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Compte utilisateur</h3>
            </div>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/login" class="hover:text-[#0099cc] transition-colors">• Connexion</a></li>
              <li><a href="/register" class="hover:text-[#0099cc] transition-colors">• Inscription</a></li>
              <li><a href="/forgot-password" class="hover:text-[#0099cc] transition-colors">• Mot de passe oublié</a></li>
              <li><a href="/verify-email" class="hover:text-[#0099cc] transition-colors">• Vérification email</a></li>
              <li><a href="/customer" class="hover:text-[#0099cc] transition-colors">• Tableau de bord</a></li>
            </ul>
          </div>

          <!-- Support et aide -->
          <div class="bg-gradient-to-br from-[#0099cc]/5 to-[#006699]/5 border border-[#0099cc]/20 rounded-2xl p-6 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-4">
              <div class="w-10 h-10 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 9.75a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375m-13.5 3.01c0 1.6 1.123 2.994 2.707 3.227 1.087.16 2.185.283 3.293.369V21l4.184-4.183a1.14 1.14 0 0 1 .778-.332 48.294 48.294 0 0 0 5.83-.498c1.585-.233 2.708-1.626 2.708-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0 0 12 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018Z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Support et aide</h3>
            </div>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/support-center" class="hover:text-[#0099cc] transition-colors">• Centre d'assistance</a></li>
              <li><a href="/contact" class="hover:text-[#0099cc] transition-colors">• Nous contacter</a></li>
              <li><a href="/report-suspicious" class="hover:text-[#0099cc] transition-colors">• Signaler une activité suspecte</a></li>
              <li><a href="/security-center" class="hover:text-[#0099cc] transition-colors">• Centre de sécurité</a></li>
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
          <div class="bg-white border-2 border-[#0099cc]/20 rounded-2xl p-8 hover:border-[#0099cc]/40 hover:shadow-lg transition-all">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 21v-7.5a.75.75 0 0 1 .75-.75h3a.75.75 0 0 1 .75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349M3.75 21V9.349m0 0a3.001 3.001 0 0 0 3.75-.615A2.993 2.993 0 0 0 9.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 0 0 2.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 0 0 3.75.614m-16.5 0a3.004 3.004 0 0 1-.621-4.72L4.318 3.44A1.5 1.5 0 0 1 5.378 3h13.243a1.5 1.5 0 0 1 1.06.44l1.19 1.189a3 3 0 0 1-.621 4.72m-13.5 8.65h3.75a.75.75 0 0 0 .75-.75V13.5a.75.75 0 0 0-.75-.75H6.75a.75.75 0 0 0-.75.75v3.75c0 .415.336.75.75.75Z" />
                </svg>
              </div>
              <h3 class="text-2xl font-bold text-gray-900">Espace marchand</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/merchant" class="hover:text-blue-600 transition-colors font-medium">• Tableau de bord</a></li>
              <li><a href="/merchant/products" class="hover:text-[#0099cc] transition-colors">• Gérer les produits</a></li>
              <li><a href="/merchant/lotteries" class="hover:text-[#0099cc] transition-colors">• Mes tombolas</a></li>
              <li><a href="/merchant/orders" class="hover:text-[#0099cc] transition-colors">• Gestion des commandes</a></li>
              <li><a href="/merchant/analytics" class="hover:text-[#0099cc] transition-colors">• Statistiques</a></li>
              <li><a href="/merchant/settings" class="hover:text-[#0099cc] transition-colors">• Paramètres</a></li>
              <li><a href="/merchant/profile" class="hover:text-[#0099cc] transition-colors">• Mon profil</a></li>
              <li><a href="/merchant/notifications" class="hover:text-[#0099cc] transition-colors">• Notifications</a></li>
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
          <div class="w-12 h-12 bg-[#0099cc]/10 rounded-full flex items-center justify-center">
            <svg class="h-6 w-6 text-[#0099cc]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Pages légales et informatives</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Pages légales</h3>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/terms" class="hover:text-[#0099cc] transition-colors">• Conditions d'utilisation</a></li>
              <li><a href="/privacy" class="hover:text-[#0099cc] transition-colors">• Politique de confidentialité</a></li>
              <li><a href="/legal" class="hover:text-[#0099cc] transition-colors">• Mentions légales</a></li>
              <li><a href="/cookies" class="hover:text-[#0099cc] transition-colors">• Politique des cookies</a></li>
              <li><a href="/intellectual-property" class="hover:text-[#0099cc] transition-colors">• Propriété intellectuelle</a></li>
              <li><a href="/lottery-participation" class="hover:text-[#0099cc] transition-colors">• Règles de participation</a></li>
            </ul>
          </div>

          <div class="bg-white border border-gray-200 rounded-2xl p-6 hover:shadow-lg transition-all">
            <h3 class="text-xl font-bold text-gray-900 mb-4">Pages informatives</h3>
            <ul class="space-y-2 text-gray-700">
              <li><a href="/about" class="hover:text-[#0099cc] transition-colors">• À propos de Koumbaya</a></li>
              <li><a href="/careers" class="hover:text-[#0099cc] transition-colors">• Carrières</a></li>
              <li><a href="/media-press" class="hover:text-[#0099cc] transition-colors">• Médias et presse</a></li>
              <li><a href="/order-delivery" class="hover:text-[#0099cc] transition-colors">• Livraison et expédition</a></li>
              <li><a href="/earn-gombos" class="hover:text-[#0099cc] transition-colors">• Gagner des Gombos</a></li>
              <li><a href="/peace-on-koumbaya" class="hover:text-[#0099cc] transition-colors">• Paix sur Koumbaya</a></li>
            </ul>
          </div>
        </div>
      </div>

      <!-- Pages partenaires -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-[#0099cc]/10 rounded-full flex items-center justify-center">
            <svg class="h-6 w-6 text-[#0099cc]" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.935-2.186 2.25 2.25 0 0 0-3.935 2.186Z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Pages partenaires</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
          <div class="bg-gradient-to-br from-[#0099cc]/5 to-[#006699]/5 border border-[#0099cc]/20 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-10 h-10 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.277-4.216.42-6.378.42s-4.291-.143-6.378-.42c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.18 2.18 0 0 0 .75-1.661V8.706c0-1.081-.768-2.015-1.837-2.175a48.114 48.114 0 0 0-3.413-.387m4.5 8.006c-.194.165-.42.295-.673.38A23.978 23.978 0 0 1 12 15.75c-2.648 0-5.195-.429-7.577-1.22a2.016 2.016 0 0 1-.673-.38m0 0A2.18 2.18 0 0 1 3 12.489V8.706c0-1.081.768-2.015 1.837-2.175a48.111 48.111 0 0 1 3.413-.387m7.5 0V5.25A2.25 2.25 0 0 0 13.5 3h-3a2.25 2.25 0 0 0-2.25 2.25v.894m7.5 0a48.667 48.667 0 0 0-7.5 0M12 12.75h.008v.008H12v-.008Z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Programmes de partenariat</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/affiliates" class="hover:text-[#0099cc] transition-colors font-medium">• Programme d'affiliation</a></li>
              <li><a href="/sell-on-koumbaya" class="hover:text-[#0099cc] transition-colors">• Vendre sur Koumbaya</a></li>
              <li class="text-gray-500">• Programme API développeurs (bientôt)</li>
              <li class="text-gray-500">• Partenariats d'entreprise (bientôt)</li>
            </ul>
          </div>

          <div class="bg-gradient-to-br from-[#0099cc]/5 to-[#006699]/5 border border-[#0099cc]/20 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-10 h-10 bg-[#0099cc] rounded-full flex items-center justify-center">
                <svg class="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17 17.25 21A2.652 2.652 0 0 0 21 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 1 1-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 0 0 4.486-6.336l-3.276 3.277a3.004 3.004 0 0 1-2.25-2.25l3.276-3.276a4.5 4.5 0 0 0-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437 1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008Z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Outils et ressources</h3>
            </div>
            <ul class="space-y-3 text-gray-700">
              <li><a href="/verify" class="hover:text-[#0099cc] transition-colors">• Vérifier un gagnant</a></li>
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
          <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
            <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="m3.75 13.5 10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75Z" />
            </svg>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Fonctionnalités spéciales</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-[#0099cc] rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z" />
              </svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Paiements</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Méthodes de paiement</li>
              <li>• Confirmation de paiement</li>
              <li>• Historique des transactions</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-[#0099cc] rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 0 0 5.454-1.31A8.967 8.967 0 0 1 18 9.75V9A6 6 0 0 0 6 9v.75a8.967 8.967 0 0 1-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 0 1-5.714 0m5.714 0a3 3 0 1 1-5.714 0" />
              </svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Notifications</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Notifications en temps réel</li>
              <li>• Alertes par email</li>
              <li>• Préférences de notifications</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-[#0099cc] rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" />
              </svg>
            </div>
            <h3 class="font-bold text-gray-900 mb-2">Applications mobiles</h3>
            <ul class="text-gray-600 text-sm space-y-1">
              <li>• Application iOS (bientôt)</li>
              <li>• Application Android (bientôt)</li>
              <li>• Version PWA disponible</li>
            </ul>
          </div>

          <div class="bg-white rounded-xl p-6 text-center">
            <div class="w-16 h-16 bg-[#0099cc] rounded-full flex items-center justify-center mx-auto mb-4">
              <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 3.03v.568c0 .334.148.65.405.864l1.068.89c.442.369.535 1.01.216 1.49l-.51.766a2.25 2.25 0 0 1-1.161.886l-.143.048a1.107 1.107 0 0 0-.57 1.664c.369.555.169 1.307-.427 1.605L9 13.125l.423 1.059a.956.956 0 0 1-1.652.928l-.679-.906a1.125 1.125 0 0 0-1.906.172L4.5 15.75l-.612.153M12.75 3.031a9 9 0 0 0-8.862 12.872M12.75 3.031a9 9 0 0 1 6.69 14.036m0 0-.177-.529A2.25 2.25 0 0 0 17.128 15H16.5l-.324-.324a1.453 1.453 0 0 0-2.328.377l-.036.073a1.586 1.586 0 0 1-.982.816l-.99.282c-.55.157-.894.702-.8 1.267l.073.438c.08.474.49.821.97.821.846 0 1.598.542 1.865 1.345l.215.643m5.276-3.67a9.012 9.012 0 0 1-5.276 3.67m0 0a9.025 9.025 0 0 1-8.862-12.872M15.75 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
              </svg>
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
      <div class="bg-gradient-to-br from-[#0099cc]/5 to-[#006699]/5 border border-[#0099cc]/20 rounded-2xl p-8">
        <div class="flex items-center gap-4 mb-6">
          <div class="w-12 h-12 bg-[#0099cc] rounded-full flex items-center justify-center">
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
              class="block w-full pl-12 pr-4 py-4 border border-gray-300 rounded-xl focus:ring-2 focus:ring-[#0099cc] focus:border-transparent text-lg"
              placeholder="Recherchez une page, une fonctionnalité... (ex: tombola, paiement, profil)"
            />
          </div>

          <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <svg class="h-8 w-8 text-[#0099cc] mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 11.25v8.25a1.5 1.5 0 0 1-1.5 1.5H5.25a1.5 1.5 0 0 1-1.5-1.5v-8.25M12 4.875A2.625 2.625 0 1 0 9.375 7.5H12m0-2.625V7.5m0-2.625A2.625 2.625 0 1 1 14.625 7.5H12m0 0V21m-8.625-9.75h18c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125h-18c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125Z" />
              </svg>
              <p class="text-sm font-medium text-gray-700">Tombolas</p>
            </div>
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <svg class="h-8 w-8 text-[#0099cc] mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m-3-2.818.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
              </svg>
              <p class="text-sm font-medium text-gray-700">Gombos</p>
            </div>
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <svg class="h-8 w-8 text-[#0099cc] mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 18.75h-9m9 0a3 3 0 0 1 3 3h-15a3 3 0 0 1 3-3m9 0v-3.375c0-.621-.503-1.125-1.125-1.125h-.871M7.5 18.75v-3.375c0-.621.504-1.125 1.125-1.125h.872m5.007 0H9.497m5.007 0a7.454 7.454 0 0 1-.982-3.172M9.497 14.25a7.454 7.454 0 0 0 .981-3.172M5.25 4.236c-.982.143-1.954.317-2.916.52A6.003 6.003 0 0 0 7.73 9.728M5.25 4.236V4.5c0 2.108.966 3.99 2.48 5.228M5.25 4.236V2.721C7.456 2.41 9.71 2.25 12 2.25c2.291 0 4.545.16 6.75.47v1.516M7.73 9.728a6.726 6.726 0 0 0 2.748 1.35m8.272-6.842V4.5c0 2.108-.966 3.99-2.48 5.228m2.48-5.228V2.721a48.336 48.336 0 0 0-6.75-.47m0 0v8.018a5.25 5.25 0 0 0-3.75 0V2.25m3.75 0c.394 0 .787.016 1.178.048m-1.178-.048a48.323 48.323 0 0 0-9.75 0V9a5.25 5.25 0 0 0 3.75 0V2.25Z" />
              </svg>
              <p class="text-sm font-medium text-gray-700">Gagnants</p>
            </div>
            <div class="bg-white rounded-lg p-4 hover:shadow-md transition-all cursor-pointer">
              <svg class="h-8 w-8 text-[#0099cc] mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
              </svg>
              <p class="text-sm font-medium text-gray-700">Sécurité</p>
            </div>
          </div>
        </div>
      </div>

      <!-- Call to Action -->
      <div class="bg-gradient-to-r from-[#0099cc] to-[#006699] rounded-2xl p-12 text-white text-center">
        <div class="max-w-3xl mx-auto space-y-6">
          <div class="mb-6">
            <svg class="h-16 w-16 text-white mx-auto" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
            </svg>
          </div>
          <h2 class="text-3xl md:text-4xl font-bold">Besoin d'aide pour naviguer ?</h2>
          <p class="text-xl text-[#0099cc]/20 leading-relaxed">
            Si vous ne trouvez pas ce que vous cherchez dans ce plan du site, notre équipe support est là pour vous guider.
          </p>
          
          <div class="flex flex-col sm:flex-row gap-4 justify-center items-center pt-6">
            <a
              href="/support-center"
              class="inline-flex items-center gap-3 bg-white text-[#0099cc] px-8 py-4 rounded-xl font-bold hover:bg-[#0099cc]/5 transition-all duration-200 hover:scale-[1.05] shadow-lg text-lg"
            >
              <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
              </svg>
              Centre d'assistance
            </a>
            
            <a
              href="/contact"
              class="inline-flex items-center gap-2 text-[#0099cc]/20 hover:text-white font-semibold transition-colors"
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