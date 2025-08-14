import { ref } from 'vue'

// Simple toast composable
export function useToast() {
  const showToast = (message, type = 'info', duration = 3000) => {
    // Pour l'instant, on utilise alert, mais on peut intégrer un vrai système de toast plus tard
    if (type === 'error') {
      console.error(message)
      alert('Erreur: ' + message)
    } else if (type === 'success') {
      console.log(message)
      alert(message)
    } else {
      console.log(message)
      alert(message)
    }
  }

  return {
    success: (message) => showToast(message, 'success'),
    error: (message) => showToast(message, 'error'),
    info: (message) => showToast(message, 'info'),
    warning: (message) => showToast(message, 'warning')
  }
}