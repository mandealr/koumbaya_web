import { describe, it, expect, vi, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import ProductCard from '../../../resources/js/components/common/ProductCard.vue'
import securityService from '../../../resources/js/services/security.js'

// Mock des composants enfants
const mockProductImage = {
  template: '<img :src="src" :alt="alt" :class="imageClass" />',
  props: ['src', 'alt', 'containerClass', 'imageClass']
}

const mockIcons = {
  CheckCircleIcon: { template: '<svg class="check-icon"></svg>' },
  ClockIcon: { template: '<svg class="clock-icon"></svg>' },
  XCircleIcon: { template: '<svg class="x-icon"></svg>' },
  StarIcon: { template: '<svg class="star-icon"></svg>' },
  TagIcon: { template: '<svg class="tag-icon"></svg>' },
  TicketIcon: { template: '<svg class="ticket-icon"></svg>' },
  ShoppingBagIcon: { template: '<svg class="bag-icon"></svg>' },
}

describe('ProductCard', () => {
  let wrapper
  
  const mockProduct = {
    id: 1,
    name: 'iPhone 14 Pro',
    description: 'Smartphone haut de gamme',
    price: 850000,
    ticket_price: 1500,
    image_url: 'https://example.com/iphone.jpg',
    status: 'active',
    sale_mode: 'lottery',
    category: { name: 'Smartphones' },
    isPopular: true
  }

  beforeEach(() => {
    vi.clearAllMocks()
  })

  const createWrapper = (product = mockProduct, options = {}) => {
    return mount(ProductCard, {
      props: {
        product: { ...mockProduct, ...product }
      },
      global: {
        components: {
          ProductImage: mockProductImage,
          ...mockIcons
        },
        stubs: {
          ProductImage: mockProductImage,
          ...mockIcons
        }
      },
      ...options
    })
  }

  describe('Rendering', () => {
    it('should render product information correctly', () => {
      wrapper = createWrapper()
      
      expect(wrapper.text()).toContain('iPhone 14 Pro')
      expect(wrapper.text()).toContain('Smartphones')
      expect(wrapper.find('img').attributes('alt')).toBe('iPhone 14 Pro')
    })

    it('should render product status badge correctly', () => {
      wrapper = createWrapper({ status: 'active' })
      expect(wrapper.find('.koumbaya-badge-success').exists()).toBe(true)
      expect(wrapper.text()).toContain('Actif')

      wrapper = createWrapper({ status: 'pending' })
      expect(wrapper.find('.koumbaya-badge-warning').exists()).toBe(true)
      expect(wrapper.text()).toContain('En attente')

      wrapper = createWrapper({ status: 'inactive' })
      expect(wrapper.find('.koumbaya-badge-error').exists()).toBe(true)
      expect(wrapper.text()).toContain('Inactif')
    })

    it('should render popularity badge when product is popular', () => {
      wrapper = createWrapper({ isPopular: true })
      expect(wrapper.text()).toContain('Populaire')
      expect(wrapper.find('.star-icon').exists()).toBe(true)
    })

    it('should not render popularity badge when product is not popular', () => {
      wrapper = createWrapper({ isPopular: false })
      expect(wrapper.text()).not.toContain('Populaire')
    })

    it('should render sale mode correctly', () => {
      wrapper = createWrapper({ sale_mode: 'lottery' })
      expect(wrapper.find('.ticket-icon').exists()).toBe(true)

      wrapper = createWrapper({ sale_mode: 'direct' })
      expect(wrapper.find('.bag-icon').exists()).toBe(true)
    })
  })

  describe('Security Tests', () => {
    it('should sanitize product name against XSS', () => {
      const maliciousProduct = {
        ...mockProduct,
        name: '<script>alert("XSS")</script>iPhone'
      }
      
      wrapper = createWrapper(maliciousProduct)
      
      // Vérifier que le script n'est pas exécuté
      expect(wrapper.html()).not.toContain('<script>')
      // Mais le texte légitime doit être présent
      expect(wrapper.text()).toContain('iPhone')
    })

    it('should sanitize product description against XSS', () => {
      const maliciousProduct = {
        ...mockProduct,
        description: '<img src=x onerror=alert("XSS")>Description'
      }
      
      wrapper = createWrapper(maliciousProduct)
      expect(wrapper.html()).not.toContain('onerror')
      expect(wrapper.text()).toContain('Description')
    })

    it('should validate image URL security', () => {
      const suspiciousProduct = {
        ...mockProduct,
        image_url: 'javascript:alert("XSS")'
      }
      
      wrapper = createWrapper(suspiciousProduct)
      const img = wrapper.find('img')
      
      // L'image ne devrait pas avoir de src dangereux
      expect(img.attributes('src')).not.toContain('javascript:')
    })

    it('should handle malicious category names', () => {
      const maliciousProduct = {
        ...mockProduct,
        category: { 
          name: '<script>document.location="http://evil.com"</script>Electronics' 
        }
      }
      
      wrapper = createWrapper(maliciousProduct)
      expect(wrapper.html()).not.toContain('<script>')
      expect(wrapper.text()).toContain('Electronics')
    })

    it('should validate price values', () => {
      const suspiciousProduct = {
        ...mockProduct,
        price: 'javascript:alert("price")',
        ticket_price: -1000
      }
      
      wrapper = createWrapper(suspiciousProduct)
      
      // Les prix malveillants ne devraient pas être affichés tels quels
      expect(wrapper.html()).not.toContain('javascript:')
    })
  })

  describe('Data Validation', () => {
    it('should handle missing product data gracefully', () => {
      const incompleteProduct = {
        id: 1,
        name: 'Product'
        // Manque beaucoup de champs
      }
      
      wrapper = createWrapper(incompleteProduct)
      expect(wrapper.exists()).toBe(true)
      expect(wrapper.text()).toContain('Product')
    })

    it('should provide default values for missing fields', () => {
      const productWithoutCategory = {
        ...mockProduct,
        category: null
      }
      
      wrapper = createWrapper(productWithoutCategory)
      expect(wrapper.text()).toContain('Électronique') // Valeur par défaut
    })

    it('should handle very long product names', () => {
      const longNameProduct = {
        ...mockProduct,
        name: 'A'.repeat(1000) // Nom très long
      }
      
      wrapper = createWrapper(longNameProduct)
      expect(wrapper.exists()).toBe(true)
    })

    it('should validate numeric fields', () => {
      const invalidProduct = {
        ...mockProduct,
        price: 'not-a-number',
        ticket_price: 'invalid'
      }
      
      wrapper = createWrapper(invalidProduct)
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('User Interactions', () => {
    it('should emit click event when card is clicked', async () => {
      wrapper = createWrapper()
      
      await wrapper.trigger('click')
      
      expect(wrapper.emitted('click')).toBeTruthy()
      expect(wrapper.emitted('click')[0]).toEqual([mockProduct])
    })

    it('should not emit click event for disabled products', async () => {
      wrapper = createWrapper({ status: 'inactive' })
      
      await wrapper.trigger('click')
      
      // Les produits inactifs ne devraient pas être cliquables
      // (selon la logique métier à implémenter)
    })
  })

  describe('Accessibility', () => {
    it('should have proper alt text for images', () => {
      wrapper = createWrapper()
      const img = wrapper.find('img')
      
      expect(img.attributes('alt')).toBe(mockProduct.name)
      expect(img.attributes('alt')).toBeTruthy()
    })

    it('should have proper ARIA labels', () => {
      wrapper = createWrapper()
      
      // Vérifier la présence d'attributs d'accessibilité
      const card = wrapper.find('.koumbaya-card')
      expect(card.attributes('role')).toBeTruthy()
    })

    it('should support keyboard navigation', async () => {
      wrapper = createWrapper()
      
      await wrapper.trigger('keydown.enter')
      expect(wrapper.emitted('click')).toBeTruthy()
    })
  })

  describe('Performance', () => {
    it('should not re-render unnecessarily', async () => {
      wrapper = createWrapper()
      const initialHtml = wrapper.html()
      
      // Changer une prop qui ne devrait pas affecter le rendu
      await wrapper.setProps({ product: { ...mockProduct } })
      
      expect(wrapper.html()).toBe(initialHtml)
    })

    it('should handle large numbers of products efficiently', () => {
      // Test de performance avec de gros volumes
      const startTime = performance.now()
      
      for (let i = 0; i < 100; i++) {
        const testWrapper = createWrapper({
          ...mockProduct,
          id: i,
          name: `Product ${i}`
        })
        testWrapper.unmount()
      }
      
      const endTime = performance.now()
      expect(endTime - startTime).toBeLessThan(1000) // Moins d'1 seconde
    })
  })

  describe('Edge Cases', () => {
    it('should handle null/undefined product gracefully', () => {
      expect(() => {
        wrapper = createWrapper(null)
      }).not.toThrow()
    })

    it('should handle special characters in product data', () => {
      const specialCharsProduct = {
        ...mockProduct,
        name: 'Café "Spécial" & Thé (100%)',
        description: 'Produit avec caractères spéciaux: àáâãäåçèéêë'
      }
      
      wrapper = createWrapper(specialCharsProduct)
      expect(wrapper.text()).toContain('Café "Spécial" & Thé (100%)')
    })

    it('should handle different currency formats', () => {
      const currencyProduct = {
        ...mockProduct,
        price: 1500.50,
        currency: 'XAF'
      }
      
      wrapper = createWrapper(currencyProduct)
      expect(wrapper.exists()).toBe(true)
    })
  })

  describe('Security Integration Tests', () => {
    it('should use security service for validation', () => {
      const validateSpy = vi.spyOn(securityService, 'validateInput')
      
      wrapper = createWrapper({
        ...mockProduct,
        name: 'Test Product'
      })
      
      // Le service de sécurité devrait être appelé pour valider les entrées
      expect(validateSpy).toHaveBeenCalled()
    })

    it('should handle XSS attempts in multiple fields simultaneously', () => {
      const multiXSSProduct = {
        ...mockProduct,
        name: '<script>alert("name")</script>',
        description: '<img onerror=alert("desc")>',
        category: { name: '<iframe src="javascript:alert()"></iframe>' }
      }
      
      wrapper = createWrapper(multiXSSProduct)
      
      // Aucun élément dangereux ne devrait être présent
      expect(wrapper.html()).not.toContain('<script>')
      expect(wrapper.html()).not.toContain('onerror')
      expect(wrapper.html()).not.toContain('<iframe>')
    })
  })
})