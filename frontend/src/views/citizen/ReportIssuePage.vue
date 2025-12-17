<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-4xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <h1 class="mb-2 text-4xl font-bold text-[#10141f]">Report a New Issue</h1>
        <p class="text-[#819796]">Help improve your community by reporting an issue</p>
      </div>

      <!-- Form Card -->
      <div class="overflow-hidden rounded-xl bg-white shadow-md">
        <form @submit.prevent="submitForm" class="p-8">
          <!-- Issue Title -->
          <div class="mb-6">
            <label for="title" class="mb-2 block text-sm font-semibold text-[#10141f]">
              Issue Title <span class="text-[#cf573c]">*</span>
            </label>
            <input
              id="title"
              v-model="form.title"
              type="text"
              placeholder="e.g., Large pothole on Main Street"
              class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
              required
            />
            <p class="mt-1 text-xs text-[#819796]">Be specific about what the issue is</p>
          </div>

          <!-- Issue Description -->
          <div class="mb-6">
            <label for="description" class="mb-2 block text-sm font-semibold text-[#10141f]">
              Description <span class="text-[#cf573c]">*</span>
            </label>
            <textarea
              id="description"
              v-model="form.description"
              placeholder="Provide more details about the issue..."
              rows="5"
              maxlength="1000"
              class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
              required
            ></textarea>
            <p class="mt-1 text-xs text-[#819796]">{{ form.description.length }}/1000 characters</p>
          </div>

          <!-- Two Column Grid -->
          <div class="mb-6 grid grid-cols-1 gap-6 md:grid-cols-2">
            <!-- Category -->
            <div>
              <label for="category" class="mb-2 block text-sm font-semibold text-[#10141f]">
                Category <span class="text-[#cf573c]">*</span>
              </label>
              <select
                id="category"
                v-model="form.category"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
                required
              >
                <option value="">Select a category</option>
                <option value="pothole">Pothole</option>
                <option value="trash">Trash/Littering</option>
                <option value="streetlight">Street Light</option>
                <option value="graffiti">Graffiti</option>
                <option value="water_leak">Water Leak</option>
                <option value="tree_damage">Tree Damage</option>
                <option value="sidewalk">Sidewalk Damage</option>
                <option value="other">Other</option>
              </select>
            </div>

            <!-- Priority -->
            <div>
              <label for="priority" class="mb-2 block text-sm font-semibold text-[#10141f]">
                Priority <span class="text-[#cf573c]">*</span>
              </label>
              <select
                id="priority"
                v-model="form.priority"
                class="w-full rounded-lg border border-gray-300 px-4 py-2 focus:ring-2 focus:ring-[#25562e] focus:outline-none"
                required
              >
                <option value="low">Low</option>
                <option value="medium">Medium</option>
                <option value="high">High</option>
                <option value="critical">Critical</option>
              </select>
            </div>
          </div>

          <!-- Map Section -->
          <div class="mb-6">
            <label class="mb-2 block text-sm font-semibold text-[#10141f]">
              Location on Map <span class="text-[#cf573c]">*</span>
            </label>
            <p class="mb-3 text-xs text-[#819796]">
              Click on the map to mark the exact location of the issue
            </p>
            <div
              id="map"
              class="relative z-0 mb-3 h-80 max-h-80 overflow-hidden rounded-lg border border-gray-300"
            ></div>

            <div
              v-if="form.latitude && form.longitude"
              class="rounded-lg border border-green-200 bg-green-50 p-3"
            >
              <p class="text-sm text-green-800">
                <i class="fas fa-check-circle mr-2"></i>
                Location selected: {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}
              </p>
            </div>
            <div v-else class="rounded-lg border border-yellow-200 bg-yellow-50 p-3">
              <p class="text-sm text-yellow-800">
                <i class="fas fa-exclamation-triangle mr-2"></i>
                Please click on the map to select a location
              </p>
            </div>
          </div>

          <!-- Image Upload -->
          <div class="mb-6">
            <label for="image" class="mb-2 block text-sm font-semibold text-[#10141f]">
              Upload Photo (Optional)
            </label>
            <div
              class="cursor-pointer rounded-lg border-2 border-dashed border-gray-300 p-6 text-center transition-colors hover:border-[#75a743]"
              @click="$refs.fileInput.click()"
              @dragover.prevent="isDragging = true"
              @dragleave.prevent="isDragging = false"
              @drop.prevent="handleFileDrop"
              :class="{ 'border-[#75a743] bg-green-50': isDragging }"
            >
              <input
                ref="fileInput"
                type="file"
                accept="image/*"
                class="hidden"
                @change="handleFileSelect"
              />
              <i class="fas fa-cloud-upload-alt mb-2 block text-3xl text-[#819796]"></i>
              <p class="font-semibold text-[#10141f]">Drag and drop your image here</p>
              <p class="text-sm text-[#819796]">or click to browse</p>
              <p class="mt-2 text-xs text-[#819796]">Supported formats: JPG, PNG, GIF (Max 5MB)</p>
            </div>

            <!-- Image Preview -->
            <div v-if="imagePreview" class="mt-4">
              <p class="mb-2 text-sm font-semibold text-[#10141f]">Preview:</p>
              <img
                :src="imagePreview"
                alt="Preview"
                class="max-h-64 rounded-lg border border-gray-300"
              />
              <button
                type="button"
                @click="removeImage"
                class="mt-2 text-sm text-[#cf573c] hover:underline"
              >
                <i class="fas fa-trash mr-1"></i>Remove image
              </button>
            </div>
            <div v-if="form.image" class="mt-3 text-sm text-green-600">
              <i class="fas fa-check-circle mr-1"></i>
              {{ form.image.name }}
            </div>
          </div>

          <!-- Terms -->
          <div class="mb-6 flex items-start gap-3">
            <input
              id="terms"
              v-model="form.agreeToTerms"
              type="checkbox"
              class="mt-1 rounded"
              required
            />
            <label for="terms" class="text-sm text-[#819796]">
              I confirm that this information is accurate and will help improve our community
            </label>
          </div>

          <!-- Submit Button -->
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="isSubmitting || !form.latitude || !form.longitude"
              class="flex-1 rounded-lg bg-gradient-to-r from-[#25562e] to-[#1a3d21] px-4 py-3 font-semibold text-white transition-shadow hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
            >
              <i v-if="isSubmitting" class="fas fa-spinner fa-spin mr-2"></i>
              {{ isSubmitting ? 'Submitting...' : 'Report Issue' }}
            </button>
            <router-link
              to="/dashboard"
              class="flex-1 rounded-lg border-2 border-gray-300 px-4 py-3 text-center font-semibold text-[#10141f] transition-colors hover:bg-gray-50"
            >
              Cancel
            </router-link>
          </div>

          <!-- Error Message -->
          <div v-if="error" class="mt-4 rounded-lg border border-red-200 bg-red-50 p-4">
            <p class="text-sm text-red-800">
              <i class="fas fa-exclamation-circle mr-2"></i>
              {{ error }}
            </p>
          </div>

          <!-- Success Message -->
          <div
            v-if="successMessage"
            class="mt-4 rounded-lg border border-green-200 bg-green-50 p-4"
          >
            <p class="text-sm text-green-800">
              <i class="fas fa-check-circle mr-2"></i>
              {{ successMessage }}
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import L from 'leaflet'
import { useIssuesStore } from '../../stores/issuesStore'
import { useRouter } from 'vue-router'

const issuesStore = useIssuesStore()
const router = useRouter()

const form = ref({
  title: '',
  description: '',
  category: '',
  priority: 'medium',
  latitude: null,
  longitude: null,
  image: null,
  agreeToTerms: false,
})

const imagePreview = ref(null)
const isDragging = ref(false)
const isSubmitting = ref(false)
const error = ref('')
const successMessage = ref('')
let map = null
let marker = null

const fileInput = ref(null)

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    validateAndSetImage(file)
  }
}

const handleFileDrop = (event) => {
  isDragging.value = false
  const files = event.dataTransfer.files
  if (files.length > 0) {
    validateAndSetImage(files[0])
  }
}

const validateAndSetImage = (file) => {
  const validTypes = ['image/jpeg', 'image/png', 'image/gif']
  const maxSize = 5 * 1024 * 1024 // 5MB

  if (!validTypes.includes(file.type)) {
    error.value = 'Please select a valid image format (JPG, PNG, GIF)'
    return
  }

  if (file.size > maxSize) {
    error.value = 'Image size must be less than 5MB'
    return
  }

  form.value.image = file
  error.value = ''

  // Create preview
  const reader = new FileReader()
  reader.onload = (e) => {
    imagePreview.value = e.target.result
  }
  reader.readAsDataURL(file)
}

const removeImage = () => {
  form.value.image = null
  imagePreview.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

const initMap = () => {
  // Cotabato City coordinates and bounds
  const cotabatoCenterLat = 7.2167
  const cotabatoCenterLng = 124.25
  const cotabatoBounds = [
    [7.0967, 124.12], // Southwest
    [7.3367, 124.38], // Northeast
  ]

  // Initialize map centered on Cotabato City
  map = L.map('map').setView([cotabatoCenterLat, cotabatoCenterLng], 13)

  // Set max bounds to Cotabato City with some buffer
  map.setMaxBounds([
    [7.0767, 124.1],
    [7.3567, 124.4],
  ])

  // Add OpenStreetMap tiles
  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
    minZoom: 11,
  }).addTo(map)

  // Add a visual bounds rectangle
  L.rectangle(cotabatoBounds, {
    color: '#25562e',
    weight: 2,
    opacity: 0.3,
    fillColor: '#75a743',
    fillOpacity: 0.05,
  }).addTo(map)

  // Handle map clicks
  map.on('click', (event) => {
    const { lat, lng } = event.latlng

    // Check if click is within Cotabato City bounds
    if (
      lat >= cotabatoBounds[0][0] &&
      lat <= cotabatoBounds[1][0] &&
      lng >= cotabatoBounds[0][1] &&
      lng <= cotabatoBounds[1][1]
    ) {
      form.value.latitude = lat
      form.value.longitude = lng

      // Remove previous marker
      if (marker) {
        map.removeLayer(marker)
      }

      // Add new marker
      marker = L.marker([lat, lng])
        .addTo(map)
        .bindPopup(
          `<strong>Issue Location</strong><br/>Lat: ${lat.toFixed(6)}<br/>Lng: ${lng.toFixed(6)}`,
        )
        .openPopup()
    } else {
      error.value = 'Please select a location within Cotabato City'
      setTimeout(() => {
        error.value = ''
      }, 3000)
    }
  })
}

const submitForm = async () => {
  // Check if user is authenticated
  const token = localStorage.getItem('token')
  if (!token) {
    error.value = 'You must be logged in to report an issue. Please log in and try again.'
    setTimeout(() => {
      router.push('/login')
    }, 2000)
    return
  }

  if (!form.value.latitude || !form.value.longitude) {
    error.value = 'Please select a location on the map'
    return
  }

  if (!form.value.agreeToTerms) {
    error.value = 'Please agree to the terms'
    return
  }

  isSubmitting.value = true
  error.value = ''
  successMessage.value = ''

  try {
    const formData = new FormData()
    formData.append('title', form.value.title)
    formData.append('description', form.value.description)
    formData.append('category', form.value.category)
    formData.append('priority', form.value.priority)
    formData.append('latitude', form.value.latitude)
    formData.append('longitude', form.value.longitude)

    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    await issuesStore.createIssue(formData)

    successMessage.value = 'Issue reported successfully! Redirecting to dashboard...'

    // Redirect after a short delay
    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch (err) {
    if (err.includes('Unauthorized') || err.includes('401')) {
      error.value = 'Your session has expired. Please log in again.'
      setTimeout(() => {
        router.push('/login')
      }, 2000)
    } else {
      error.value = err || 'Failed to report issue. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  initMap()
})
</script>
