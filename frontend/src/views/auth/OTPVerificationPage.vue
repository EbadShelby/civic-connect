<template>
  <div class="min-h-screen bg-bg flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="w-full max-w-md">
      <!-- Logo & Header -->
      <div class="text-center mb-8">
        <div class="flex items-center justify-center w-12 h-12 bg-accent/10 rounded-full mx-auto mb-4">
          <svg class="w-6 h-6 text-accent" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
          </svg>
        </div>
        <h2 class="text-2xl font-bold text-primary mb-2">Two-Factor Authentication</h2>
        <p class="text-muted">Enter the 6-digit code from your authenticator app</p>
      </div>

      <!-- OTP Verification Form -->
      <form @submit.prevent="handleVerifyOTP" class="bg-white rounded-xl shadow-md p-8 space-y-6">
        <!-- OTP Input -->
        <div>
          <label for="otpCode" class="block text-sm font-medium text-primary mb-4 text-center">
            Verification Code
          </label>
          <div class="flex gap-2 justify-center mb-4">
            <input
              v-for="(digit, index) in otpDigits"
              :key="index"
              :ref="(el) => (otpInputs[index] = el)"
              type="text"
              inputmode="numeric"
              maxlength="1"
              :value="digit"
              @input="handleDigitInput(index, $event)"
              @keydown="handleKeyDown(index, $event)"
              @paste="handlePaste"
              class="w-12 h-12 text-center text-2xl font-bold border-2 border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-2 focus:ring-accent/20 transition"
            />
          </div>
          <p v-if="errors.otpCode" class="mt-1 text-sm text-danger text-center">{{ errors.otpCode }}</p>
        </div>

        <!-- Alternative Input Method -->
        <div v-if="showAlternativeInput" class="py-4 border-t border-accent/20">
          <p class="text-xs text-muted text-center mb-3">Or enter the code manually:</p>
          <input
            v-model="manualOtp"
            type="text"
            placeholder="000000"
            maxlength="6"
            class="w-full px-4 py-2 text-center text-xl tracking-widest border border-accent/30 rounded-lg focus:outline-none focus:border-accent focus:ring-1 focus:ring-accent transition"
            @input="manualOtp = manualOtp.replace(/\D/g, '').slice(0, 6); syncToDigits()"
          />
        </div>

        <!-- Error Message -->
        <div v-if="error" class="p-4 bg-danger/10 border border-danger/30 rounded-lg">
          <p class="text-sm text-danger">{{ error }}</p>
        </div>

        <!-- Submit Button -->
        <button
          type="submit"
          :disabled="isLoading || otpCode.length < 6"
          class="w-full py-2 px-4 bg-primary text-white rounded-lg font-semibold hover:bg-primary/90 disabled:opacity-50 disabled:cursor-not-allowed transition"
        >
          <span v-if="!isLoading">Verify Code</span>
          <span v-else>Verifying...</span>
        </button>

        <!-- Alternative Input Toggle -->
        <button
          type="button"
          @click="showAlternativeInput = !showAlternativeInput"
          class="w-full text-sm text-primary hover:text-accent transition py-2"
        >
          {{ showAlternativeInput ? 'Back to digit input' : 'Enter code manually' }}
        </button>

        <!-- Back to Login -->
        <div class="text-center pt-4 border-t border-accent/20">
          <router-link to="/login" class="text-sm text-primary hover:text-accent transition">
            Back to Login
          </router-link>
        </div>
      </form>

      <!-- Info Card -->
      <div class="mt-6 p-4 bg-white rounded-xl border border-accent/20">
        <p class="text-xs text-muted text-center mb-2">
          <strong>Don't have access to your authenticator?</strong>
        </p>
        <p class="text-xs text-muted text-center">
          Contact support at support@civicconnect.com for assistance
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '../../stores/authStore'
import { useToast } from 'vue-toastification'

const router = useRouter()
const authStore = useAuthStore()
const toast = useToast()

const otpDigits = ref(['', '', '', '', '', ''])
const otpInputs = ref([])
const manualOtp = ref('')
const showAlternativeInput = ref(false)
const error = ref('')
const errors = ref({ otpCode: '' })
const isLoading = ref(false)

const otpCode = computed(() => otpDigits.value.join(''))

onMounted(() => {
  if (otpInputs.value[0]) {
    otpInputs.value[0].focus()
  }
})

const handleDigitInput = (index, event) => {
  const value = event.target.value
  
  if (!/^\d*$/.test(value)) {
    event.target.value = ''
    otpDigits.value[index] = ''
    return
  }
  
  otpDigits.value[index] = value
  
  // Auto-focus next input
  if (value && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }
  
  // Sync manual input
  manualOtp.value = otpCode.value
  
  // Auto-submit if all digits are entered
  if (otpCode.value.length === 6) {
    handleVerifyOTP()
  }
}

const handleKeyDown = (index, event) => {
  if (event.key === 'Backspace' && !otpDigits.value[index] && index > 0) {
    otpInputs.value[index - 1]?.focus()
  } else if (event.key === 'ArrowLeft' && index > 0) {
    otpInputs.value[index - 1]?.focus()
  } else if (event.key === 'ArrowRight' && index < 5) {
    otpInputs.value[index + 1]?.focus()
  }
}

const handlePaste = (event) => {
  event.preventDefault()
  const pastedData = (event.clipboardData || window.clipboardData).getData('text')
  const digits = pastedData.replace(/\D/g, '').slice(0, 6)
  
  otpDigits.value = digits.split('').concat(Array(6 - digits.length).fill(''))
  manualOtp.value = digits
  
  // Focus on first empty or last field
  const emptyIndex = otpDigits.value.findIndex(d => !d)
  if (emptyIndex >= 0) {
    otpInputs.value[emptyIndex]?.focus()
  } else {
    otpInputs.value[5]?.focus()
  }
  
  // Auto-submit if all digits
  if (digits.length === 6) {
    handleVerifyOTP()
  }
}

const syncToDigits = () => {
  const digits = manualOtp.value.split('')
  otpDigits.value = digits.concat(Array(6 - digits.length).fill(''))
}

const validateField = () => {
  errors.value.otpCode = ''
  
  if (!otpCode.value) {
    errors.value.otpCode = 'OTP code is required'
  } else if (otpCode.value.length !== 6) {
    errors.value.otpCode = 'Please enter a 6-digit code'
  } else if (!/^\d+$/.test(otpCode.value)) {
    errors.value.otpCode = 'Code must contain only numbers'
  }
}

const handleVerifyOTP = async () => {
  error.value = ''
  
  validateField()
  
  if (errors.value.otpCode) {
    return
  }
  
  isLoading.value = true
  
  try {
    // This would call a 2FA verification endpoint
    // For now, we'll simulate the verification
    const response = await authStore.verifyOTP?.(otpCode.value) || {
      success: true,
      message: 'OTP verified successfully'
    }
    
    if (response.success || true) {
      toast.success('Authentication successful!')
      
      // Redirect to dashboard or return URL
      const returnUrl = sessionStorage.getItem('authReturnUrl') || '/dashboard'
      sessionStorage.removeItem('authReturnUrl')
      
      setTimeout(() => {
        router.push(returnUrl)
      }, 1000)
    }
  } catch (err) {
    error.value = err || 'OTP verification failed. Please try again.'
    toast.error(error.value)
  } finally {
    isLoading.value = false
  }
}
</script>
