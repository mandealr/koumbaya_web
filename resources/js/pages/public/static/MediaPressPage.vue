<template>
  <StaticPageLayout
    title="Médias & Presse"
    description="Collaborons pour partager les histoires inspirantes de notre communauté"
    meta-description="Collaborons pour partager les histoires inspirantes de notre communauté et écosystème"
  >
    <div class="space-y-12">
      <!-- Introduction -->
      <div class="text-center space-y-6">
        <div class="w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-full flex items-center justify-center mx-auto">
          <svg class="h-8 w-8 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 18.75a6 6 0 0 0 6-6v-1.5m-6 7.5a6 6 0 0 1-6-6v-1.5m6 7.5v3.75m-3.75 0h7.5M12 15.75a3 3 0 0 1-3-3V4.5a3 3 0 1 1 6 0v8.25a3 3 0 0 1-3 3Z" />
          </svg>
        </div>
        
        <div class="max-w-4xl mx-auto">
          <p class="text-xl text-gray-700 leading-relaxed">
            Nous aimons collaborer avec vous et partager des <strong class="text-purple-600">histoires inspirantes</strong> sur notre communauté de clients, nos partenaires de l'écosystème et nos partenaires marchands.
          </p>
        </div>
      </div>

      <!-- Contact Presse -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">📧</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Contactez notre équipe média</h2>
        </div>

        <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-8">
          <div class="text-center space-y-6">
            <div class="w-20 h-20 bg-blue-600 rounded-full flex items-center justify-center mx-auto">
              <svg class="h-10 w-10 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
            </div>
            
            <div>
              <h3 class="text-2xl font-bold text-gray-900 mb-4">Contactez-nous par e-mail</h3>
              <a 
                href="mailto:media@koumbaya.com" 
                class="inline-flex items-center gap-3 text-2xl font-semibold text-blue-600 hover:text-blue-700 transition-colors"
              >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                </svg>
                media@koumbaya.com
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Informations importantes -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-yellow-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">🔔</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Informations importantes</h2>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
          <!-- Pour la presse -->
          <div class="bg-gradient-to-br from-green-50 to-emerald-50 border border-green-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-green-600 rounded-full flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Membres de la presse</h3>
            </div>
            <p class="text-gray-700 text-lg leading-relaxed">
              <strong class="text-green-600">Seuls les membres de la presse recevront une réponse.</strong>
            </p>
            <p class="text-gray-600 mt-4">
              Notre équipe média est dédiée exclusivement aux demandes journalistiques et relations presse.
            </p>
          </div>

          <!-- Pour les clients -->
          <div class="bg-gradient-to-br from-blue-50 to-indigo-50 border border-blue-200 rounded-2xl p-8">
            <div class="flex items-center gap-4 mb-6">
              <div class="w-12 h-12 bg-blue-600 rounded-full flex items-center justify-center">
                <svg class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                </svg>
              </div>
              <h3 class="text-xl font-bold text-gray-900">Clients Koumbaya</h3>
            </div>
            <p class="text-gray-700 text-lg leading-relaxed mb-4">
              Si vous êtes un client et avez des questions concernant Koumbaya, veuillez consulter :
            </p>
            <a 
              href="/support-center" 
              class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-semibold transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
              </svg>
              koumbaya.com/support-center
            </a>
          </div>
        </div>
      </div>

      <!-- Autres demandes -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-purple-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">📌</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Autres demandes</h2>
        </div>

        <div class="bg-gradient-to-br from-purple-50 to-pink-50 border border-purple-200 rounded-2xl p-8">
          <div class="flex items-start gap-4">
            <svg class="h-8 w-8 text-purple-600 mt-1 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
            </svg>
            <div>
              <h3 class="text-xl font-semibold text-gray-900 mb-4">Pour toute demande non liée aux médias</h3>
              <p class="text-gray-700 text-lg leading-relaxed mb-4">
                Rendez-vous sur le Centre d'assistance Koumbaya pour obtenir de l'aide sur tous les autres sujets.
              </p>
              <a 
                href="/support-center" 
                class="inline-flex items-center gap-3 bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-xl font-semibold transition-colors"
              >
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                </svg>
                Centre d'assistance Koumbaya
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Réseaux sociaux -->
      <div class="space-y-8">
        <div class="flex items-center gap-4 mb-8">
          <div class="w-12 h-12 bg-gradient-to-br from-pink-100 to-red-100 rounded-full flex items-center justify-center">
            <div class="text-2xl">🌐</div>
          </div>
          <h2 class="text-3xl font-bold text-gray-900">Suivez-nous sur les réseaux sociaux</h2>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
          <!-- Facebook -->
          <a 
            href="#" 
            class="group bg-white hover:bg-blue-50 border-2 border-gray-200 hover:border-blue-300 rounded-2xl p-6 text-center transition-all hover:scale-105 hover:shadow-lg"
          >
            <div class="w-16 h-16 bg-blue-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z" clip-rule="evenodd"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-blue-600 transition-colors">Facebook</h3>
          </a>

          <!-- X (Twitter) -->
          <a 
            href="#" 
            class="group bg-white hover:bg-gray-50 border-2 border-gray-200 hover:border-gray-400 rounded-2xl p-6 text-center transition-all hover:scale-105 hover:shadow-lg"
          >
            <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-gray-600 transition-colors">X (Twitter)</h3>
          </a>

          <!-- TikTok -->
          <a 
            href="#" 
            class="group bg-white hover:bg-pink-50 border-2 border-gray-200 hover:border-pink-300 rounded-2xl p-6 text-center transition-all hover:scale-105 hover:shadow-lg"
          >
            <div class="w-16 h-16 bg-gray-900 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:text-pink-600 transition-colors">TikTok</h3>
          </a>

          <!-- Instagram -->
          <a 
            href="#" 
            class="group bg-white hover:bg-gradient-to-br hover:from-purple-50 hover:to-pink-50 border-2 border-gray-200 hover:border-pink-300 rounded-2xl p-6 text-center transition-all hover:scale-105 hover:shadow-lg"
          >
            <div class="w-16 h-16 bg-gradient-to-br from-purple-600 to-pink-600 rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
              <svg class="h-8 w-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path fill-rule="evenodd" d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 6.618 5.367 11.986 11.988 11.986s11.987-5.368 11.987-11.986C24.004 5.367 18.635.001 12.017.001zM8.449 16.988c-1.297 0-2.448-.49-3.323-1.297C4.198 14.895 3.68 13.729 3.68 12.417s.518-2.478 1.446-3.274c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.796 1.446 1.962 1.446 3.274s-.518 2.478-1.446 3.274c-.875.807-2.026 1.297-3.323 1.297zm7.718 0c-1.297 0-2.448-.49-3.323-1.297-.928-.796-1.446-1.962-1.446-3.274s.518-2.478 1.446-3.274c.875-.807 2.026-1.297 3.323-1.297s2.448.49 3.323 1.297c.928.796 1.446 1.962 1.446 3.274s-.518 2.478-1.446 3.274c-.875.807-2.026 1.297-3.323 1.297z" clip-rule="evenodd"/>
              </svg>
            </div>
            <h3 class="font-semibold text-gray-900 group-hover:bg-gradient-to-r group-hover:from-purple-600 group-hover:to-pink-600 group-hover:bg-clip-text group-hover:text-transparent transition-colors">Instagram</h3>
          </a>
        </div>
      </div>

      <!-- Call to Action final -->
      <div class="bg-gradient-to-r from-purple-600 to-indigo-600 rounded-2xl p-8 text-white text-center">
        <div class="max-w-2xl mx-auto space-y-4">
          <div class="text-4xl mb-4">📻</div>
          <h2 class="text-2xl md:text-3xl font-bold">Travaillons ensemble</h2>
          <p class="text-purple-100 text-lg">
            Nous sommes toujours ravis de partager les histoires inspirantes de notre communauté avec les médias et la presse.
          </p>
          <div class="pt-4">
            <a 
              href="mailto:media@koumbaya.com"
              class="inline-flex items-center gap-3 bg-white text-purple-600 px-6 py-3 rounded-xl font-semibold hover:bg-purple-50 transition-colors"
            >
              <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 21.75 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
              </svg>
              Contactez notre équipe média
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