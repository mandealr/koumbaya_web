/**
 * Utilitaire pour gérer les images de produits avec support base64
 */

/**
 * Retourne l'URL correcte pour une image de produit
 * @param {Object} product - L'objet produit
 * @param {string} fallback - Image par défaut si aucune image
 * @returns {string} URL de l'image
 */
export function getProductImageUrl(product, fallback = '/images/products/placeholder.jpg') {
  if (!product) return fallback;
  
  // Priorité : image_url > main_image > image > images[0] > fallback
  const imageSource = product.image_url 
    || product.main_image 
    || product.image 
    || (product.images && product.images.length > 0 ? product.images[0] : null);
    
  if (!imageSource) return fallback;
  
  // Si c'est déjà du base64 ou une URL complète, retourner tel quel
  if (imageSource.startsWith('data:image/') || imageSource.startsWith('http')) {
    return imageSource;
  }
  
  // Si c'est un chemin qui commence par /, l'utiliser tel quel
  if (imageSource.startsWith('/')) {
    return imageSource;
  }
  
  // Sinon, construire l'URL avec notre nouvelle route API
  // Format attendu : products/YYYY/MM/filename.ext
  const pathParts = imageSource.split('/');
  if (pathParts.length >= 3) {
    const [folder, year, month, filename] = pathParts;
    if (folder === 'products' && year && month && filename) {
      return `/api/products/images/${year}/${month}/${filename}`;
    }
  }
  
  // Fallback: essayer de construire avec la date actuelle
  const now = new Date();
  const year = now.getFullYear();
  const month = String(now.getMonth() + 1).padStart(2, '0');
  return `/api/products/images/${year}/${month}/${imageSource}`;
}

/**
 * Vérifie si une chaîne est une image base64
 * @param {string} str - La chaîne à vérifier
 * @returns {boolean}
 */
export function isBase64Image(str) {
  return typeof str === 'string' && str.startsWith('data:image/');
}

/**
 * Obtient le type MIME d'une image base64
 * @param {string} base64 - L'image base64
 * @returns {string} Type MIME (ex: 'image/jpeg')
 */
export function getBase64ImageType(base64) {
  if (!isBase64Image(base64)) return null;
  
  const match = base64.match(/data:image\/([^;]+);base64/);
  return match ? `image/${match[1]}` : null;
}

/**
 * Convertit une image base64 en File object (pour upload)
 * @param {string} base64 - L'image base64
 * @param {string} filename - Nom du fichier
 * @returns {File|null}
 */
export function base64ToFile(base64, filename = 'image') {
  if (!isBase64Image(base64)) return null;
  
  try {
    // Extraire les données base64
    const arr = base64.split(',');
    const mime = arr[0].match(/:(.*?);/)[1];
    const bstr = atob(arr[1]);
    let n = bstr.length;
    const u8arr = new Uint8Array(n);
    
    while (n--) {
      u8arr[n] = bstr.charCodeAt(n);
    }
    
    // Déterminer l'extension
    const ext = mime.split('/')[1];
    const fullFilename = filename.includes('.') ? filename : `${filename}.${ext}`;
    
    return new File([u8arr], fullFilename, { type: mime });
  } catch (error) {
    console.error('Erreur lors de la conversion base64 vers File:', error);
    return null;
  }
}

/**
 * Valide qu'une image base64 n'est pas corrompue
 * @param {string} base64 - L'image base64
 * @returns {Promise<boolean>}
 */
export function validateBase64Image(base64) {
  return new Promise((resolve) => {
    if (!isBase64Image(base64)) {
      resolve(false);
      return;
    }
    
    const img = new Image();
    img.onload = () => resolve(true);
    img.onerror = () => resolve(false);
    img.src = base64;
  });
}

/**
 * Redimensionne une image base64
 * @param {string} base64 - L'image base64 source
 * @param {number} maxWidth - Largeur maximale
 * @param {number} maxHeight - Hauteur maximale
 * @param {number} quality - Qualité JPEG (0-1)
 * @returns {Promise<string>} Image base64 redimensionnée
 */
export function resizeBase64Image(base64, maxWidth = 800, maxHeight = 600, quality = 0.8) {
  return new Promise((resolve, reject) => {
    if (!isBase64Image(base64)) {
      reject(new Error('Source n\'est pas une image base64 valide'));
      return;
    }
    
    const img = new Image();
    img.onload = () => {
      const canvas = document.createElement('canvas');
      const ctx = canvas.getContext('2d');
      
      // Calculer les nouvelles dimensions en gardant le ratio
      let { width, height } = img;
      
      if (width > height) {
        if (width > maxWidth) {
          height = (height * maxWidth) / width;
          width = maxWidth;
        }
      } else {
        if (height > maxHeight) {
          width = (width * maxHeight) / height;
          height = maxHeight;
        }
      }
      
      canvas.width = width;
      canvas.height = height;
      
      ctx.drawImage(img, 0, 0, width, height);
      
      const resizedBase64 = canvas.toDataURL('image/jpeg', quality);
      resolve(resizedBase64);
    };
    
    img.onerror = () => reject(new Error('Erreur lors du chargement de l\'image'));
    img.src = base64;
  });
}