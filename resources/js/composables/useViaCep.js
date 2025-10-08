import { ref, reactive } from 'vue'
import { useToast } from 'primevue/usetoast'

/**
 * Composable for ViaCEP integration
 * Provides CEP formatting, validation, and automatic address filling
 */
export function useViaCep() {
  const toast = useToast()
  const isLoading = ref(false)
  
  // Reactive object to store address data
  const addressData = reactive({
    address: '',
    neighborhood: '',
    city: '',
    state: '',
    cep: ''
  })

  // Reactive object for errors
  const errors = reactive({
    cep: '',
    address: '',
    neighborhood: '',
    city: '',
    state: ''
  })

  /**
   * Format CEP with mask (00000-000)
   * @param {string} cep - Raw CEP input
   * @returns {string} Formatted CEP
   */
  const formatCep = (cep) => {
    // Remove all non-numeric characters
    const numericCep = cep.replace(/\D/g, '')
    
    // Apply CEP mask (00000-000)
    if (numericCep.length <= 5) {
      return numericCep
    } else {
      return numericCep.slice(0, 5) + '-' + numericCep.slice(5, 8)
    }
  }

  /**
   * Validate CEP format
   * @param {string} cep - CEP to validate
   * @returns {boolean} True if valid
   */
  const isValidCep = (cep) => {
    const numericCep = cep.replace(/\D/g, '')
    return numericCep.length === 8
  }

  /**
   * Fetch address data from ViaCEP API
   * @param {string} cep - CEP to search
   * @param {Object} targetObject - Object to update with address data (optional)
   * @returns {Promise<Object|null>} Address data or null if error
   */
  const fetchAddressByCep = async (cep, targetObject = null) => {
    const numericCep = cep.replace(/\D/g, '')
    
    if (!isValidCep(cep)) {
      errors.cep = 'CEP deve ter 8 dígitos'
      return null
    }

    isLoading.value = true
    errors.cep = ''

    try {
      const response = await fetch(`https://viacep.com.br/ws/${numericCep}/json/`)
      const data = await response.json()

      if (data.erro) {
        errors.cep = 'CEP não encontrado'
        return null
      }

      // Update internal addressData
      addressData.address = data.logradouro || ''
      addressData.neighborhood = data.bairro || ''
      addressData.city = data.localidade || ''
      addressData.state = data.uf || ''
      addressData.cep = formatCep(numericCep)

      // Update target object if provided
      if (targetObject) {
        if (targetObject.address !== undefined) targetObject.address = data.logradouro || ''
        if (targetObject.neighborhood !== undefined) targetObject.neighborhood = data.bairro || ''
        if (targetObject.city !== undefined) targetObject.city = data.localidade || ''
        if (targetObject.state !== undefined) targetObject.state = data.uf || ''
        if (targetObject.postal_code !== undefined) targetObject.postal_code = formatCep(numericCep)
        if (targetObject.cep !== undefined) targetObject.cep = formatCep(numericCep)
      }

      // Clear related errors
      errors.address = ''
      errors.neighborhood = ''
      errors.city = ''
      errors.state = ''

      // Show success message
      toast.add({
        severity: 'success',
        summary: 'Sucesso',
        detail: 'Endereço preenchido automaticamente!',
        life: 2000
      })

      return {
        address: data.logradouro || '',
        neighborhood: data.bairro || '',
        city: data.localidade || '',
        state: data.uf || '',
        cep: formatCep(numericCep),
        postal_code: formatCep(numericCep)
      }

    } catch (error) {
      console.error('Error fetching CEP:', error)
      errors.cep = 'Erro ao buscar CEP. Verifique sua conexão.'
      
      toast.add({
        severity: 'error',
        summary: 'Erro',
        detail: 'Erro ao buscar CEP. Verifique sua conexão.',
        life: 3000
      })
      
      return null
    } finally {
      isLoading.value = false
    }
  }

  /**
   * Clear all address data and errors
   */
  const clearAddressData = () => {
    addressData.address = ''
    addressData.neighborhood = ''
    addressData.city = ''
    addressData.state = ''
    addressData.cep = ''
    
    errors.cep = ''
    errors.address = ''
    errors.neighborhood = ''
    errors.city = ''
    errors.state = ''
  }

  /**
   * Set address data manually
   * @param {Object} data - Address data object
   */
  const setAddressData = (data) => {
    if (data.address !== undefined) addressData.address = data.address
    if (data.neighborhood !== undefined) addressData.neighborhood = data.neighborhood
    if (data.city !== undefined) addressData.city = data.city
    if (data.state !== undefined) addressData.state = data.state
    if (data.cep !== undefined) addressData.cep = data.cep
  }

  return {
    // State
    isLoading,
    addressData,
    errors,
    
    // Methods
    formatCep,
    isValidCep,
    fetchAddressByCep,
    clearAddressData,
    setAddressData
  }
}