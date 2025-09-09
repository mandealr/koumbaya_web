<template>
  <div class="min-h-screen bg-white">
    <!-- Hero Section -->
    <section class="bg-gradient-to-br from-[#0099cc] via-[#0088bb] to-[#0077aa] text-white py-20">
      <div class="max-w-4xl mx-auto text-center px-4 sm:px-6 lg:px-8">
        <h1 class="text-4xl md:text-6xl font-bold mb-6">
          {{ title }}
        </h1>
        <p v-if="description" class="text-xl md:text-2xl text-blue-100 leading-relaxed">
          {{ description }}
        </p>
      </div>
    </section>

    <!-- Main Content -->
    <section class="py-16 lg:py-24">
      <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="prose prose-lg max-w-none">
          <slot></slot>
        </div>
      </div>
    </section>
  </div>
</template>

<script setup>
const props = defineProps({
  title: {
    type: String,
    required: true
  },
  description: {
    type: String,
    default: ''
  },
  metaDescription: {
    type: String,
    default: ''
  }
})
</script>

<style scoped>
/* Prose styling pour le contenu */
:deep(.prose) {
  color: #374151;
  max-width: none;
}

:deep(.prose h1) {
  color: #111827;
  font-weight: 700;
  font-size: 2.25rem;
  line-height: 2.5rem;
  margin-bottom: 2rem;
}

:deep(.prose h2) {
  color: #111827;
  font-weight: 600;
  font-size: 1.875rem;
  line-height: 2.25rem;
  margin-top: 3rem;
  margin-bottom: 1.5rem;
}

:deep(.prose h3) {
  color: #111827;
  font-weight: 600;
  font-size: 1.5rem;
  line-height: 2rem;
  margin-top: 2.5rem;
  margin-bottom: 1rem;
}

:deep(.prose p) {
  margin-bottom: 1.5rem;
  line-height: 1.75;
}

:deep(.prose ul) {
  margin: 1.5rem 0;
  padding-left: 1.5rem;
}

:deep(.prose li) {
  margin-bottom: 0.5rem;
}

:deep(.prose a) {
  color: #0099cc;
  text-decoration: none;
  font-weight: 500;
  transition: color 0.15s ease-in-out;
}

:deep(.prose a:hover) {
  color: #0088bb;
  text-decoration: underline;
}

:deep(.prose strong) {
  font-weight: 600;
  color: #111827;
}

:deep(.prose blockquote) {
  border-left: 4px solid #0099cc;
  padding-left: 1.5rem;
  margin: 2rem 0;
  font-style: italic;
  color: #4b5563;
}
</style>