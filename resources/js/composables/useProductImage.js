/**
 * Composable pour gérer les images de produits
 */
export function useProductImage() {
  
  /**
   * Obtenir l'URL de l'image principale d'un produit
   * @param {Object} product - L'objet produit
   * @returns {string|null} - L'URL de l'image ou null
   */
  const getProductImageUrl = (product) => {
    if (!product) return null;
    
    // Ordre de priorité pour les images
    // 1. image_url (URL processsée par le modèle)
    // 2. main_image (première image disponible)
    // 3. images[0] (premier élément du tableau)
    // 4. image (champ image simple)
    
    if (product.image_url) {
      return product.image_url;
    }
    
    if (product.main_image) {
      return product.main_image;
    }
    
    if (product.images && Array.isArray(product.images) && product.images.length > 0) {
      return product.images[0];
    }
    
    if (product.image) {
      return product.image;
    }
    
    return null;
  };
  
  /**
   * Obtenir l'URL de l'image avec fallback vers placeholder
   * @param {Object} product - L'objet produit
   * @param {string} placeholder - URL du placeholder par défaut
   * @returns {string} - L'URL de l'image ou du placeholder
   */
  const getProductImageUrlWithFallback = (product, placeholder = '/images/products/placeholder.jpg') => {
    const imageUrl = getProductImageUrl(product);
    return imageUrl || placeholder;
  };
  
  /**
   * Vérifier si un produit a une image
   * @param {Object} product - L'objet produit
   * @returns {boolean}
   */
  const hasProductImage = (product) => {
    return getProductImageUrl(product) !== null;
  };
  
  /**
   * Obtenir toutes les images d'un produit
   * @param {Object} product - L'objet produit
   * @returns {Array} - Tableau des URLs d'images
   */
  const getAllProductImages = (product) => {
    if (!product) return [];
    
    const images = [];
    
    // Ajouter image_url si disponible
    if (product.image_url) {
      images.push(product.image_url);
    }
    
    // Ajouter les images du tableau si disponible
    if (product.images && Array.isArray(product.images)) {
      product.images.forEach(img => {
        if (img && !images.includes(img)) {
          images.push(img);
        }
      });
    }
    
    // Ajouter l'image simple si disponible et pas déjà incluse
    if (product.image && !images.includes(product.image)) {
      images.push(product.image);
    }
    
    return images;
  };
  
  return {
    getProductImageUrl,
    getProductImageUrlWithFallback,
    hasProductImage,
    getAllProductImages
  };
}