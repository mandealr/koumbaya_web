/**
 * Utilitaires pour la gestion des images et placeholders
 */

/**
 * Créer un SVG placeholder
 * @param {string} text - Texte à afficher
 * @param {number} width - Largeur de l'image
 * @param {number} height - Hauteur de l'image
 * @param {string} bgColor - Couleur de fond (hex)
 * @param {string} textColor - Couleur du texte (hex)
 * @returns {string} URL data du SVG
 */
export const createPlaceholderSVG = (
  text = 'Image', 
  width = 400, 
  height = 300, 
  bgColor = '#e2e8f0', 
  textColor = '#64748b'
) => {
  const fontSize = Math.min(width, height) / 15 // Taille proportionnelle
  const svgContent = `
    <svg width="${width}" height="${height}" xmlns="http://www.w3.org/2000/svg">
      <rect width="100%" height="100%" fill="${bgColor}"/>
      <text x="50%" y="50%" dominant-baseline="middle" text-anchor="middle" 
            fill="${textColor}" font-family="Arial, sans-serif" font-size="${fontSize}" font-weight="bold">
        ${text}
      </text>
    </svg>
  `
  return `data:image/svg+xml;base64,${btoa(svgContent)}`
}

/**
 * Obtenir l'URL d'une image avec fallback vers placeholder
 * @param {object} item - Objet contenant les données d'image
 * @param {string} fallbackText - Texte du placeholder
 * @param {number} width - Largeur du placeholder
 * @param {number} height - Hauteur du placeholder
 * @returns {string} URL de l'image ou placeholder
 */
export const getImageUrl = (item, fallbackText = 'Image', width = 400, height = 300) => {
  if (!item) return createPlaceholderSVG(fallbackText, width, height)
  
  // Essayer différentes sources d'image selon l'objet
  const imageUrl = item.image_url || 
                  item.main_image || 
                  item.image || 
                  item.avatar_url ||
                  (item.images && item.images[0])
  
  if (imageUrl) {
    // Si l'URL commence par http, l'utiliser directement
    if (imageUrl.startsWith('http')) {
      return imageUrl
    }
    // Sinon ajouter le préfixe du domaine
    return imageUrl.startsWith('/') ? imageUrl : `/storage/${imageUrl}`
  }
  
  // Retourner placeholder si aucune image disponible
  return createPlaceholderSVG(fallbackText, width, height)
}

/**
 * Handler pour les erreurs d'image
 * @param {Event} event - Événement d'erreur
 * @param {string} fallbackText - Texte du placeholder
 */
export const handleImageError = (event, fallbackText = 'Image non disponible') => {
  console.log('Image loading error, using placeholder')
  const img = event.target
  const width = img.naturalWidth || img.width || 400
  const height = img.naturalHeight || img.height || 300
  img.src = createPlaceholderSVG(fallbackText, width, height)
}

/**
 * Placeholders prédéfinis pour différents types de contenu
 */
export const PLACEHOLDERS = {
  product: (w = 400, h = 300) => createPlaceholderSVG('Produit', w, h),
  user: (w = 128, h = 128) => createPlaceholderSVG('Utilisateur', w, h, '#3b82f6', '#ffffff'),
  avatar: (w = 64, h = 64) => createPlaceholderSVG('Avatar', w, h, '#6366f1', '#ffffff'),
  logo: (w = 200, h = 80) => createPlaceholderSVG('Logo', w, h, '#1f2937', '#ffffff'),
  banner: (w = 800, h = 200) => createPlaceholderSVG('Bannière', w, h, '#374151', '#ffffff')
}