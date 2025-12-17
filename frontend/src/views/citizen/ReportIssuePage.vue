```
<template>
  <div class="min-h-screen bg-gradient-to-br from-[#ebede9] to-gray-100">
    <div class="mx-auto max-w-3xl px-4 py-8">
      <!-- Header -->
      <div class="mb-8">
        <router-link
          to="/issues"
          class="text-accent mb-4 inline-flex items-center gap-2 font-semibold hover:underline"
        >
          <ArrowLeftIcon class="h-5 w-5" />
          Back to Issues
        </router-link>
        <h1 class="mb-2 text-4xl font-bold text-[#10141f]">Report an Issue</h1>
        <p class="text-[#819796]">Help improve our community by reporting problems you see.</p>
      </div>

      <!-- Form Card -->
      <div class="overflow-hidden rounded-xl bg-white shadow-md">
        <!-- Progress Steps (Optional improvement, not in original but nice to have structure) -->

        <form @submit.prevent="submitIssue" class="space-y-8 p-8">
          <!-- Category Selection -->
          <div>
            <label class="mb-2 block text-sm font-bold text-[#10141f]"
              >Issue Category <span class="text-red-500">*</span></label
            >
            <div class="grid grid-cols-2 gap-3 md:grid-cols-4">
              <label v-for="cat in categories" :key="cat.value" class="relative cursor-pointer">
                <input
                  type="radio"
                  v-model="form.category"
                  :value="cat.value"
                  class="peer sr-only"
                />
                <div
                  class="rounded-lg border-2 border-gray-200 p-3 text-center transition-all peer-checked:border-[#75a743] peer-checked:bg-[#eaf4e1] hover:border-gray-300"
                >
                  <span class="block text-sm font-semibold text-[#10141f]">{{ cat.label }}</span>
                </div>
              </label>
            </div>
            <p v-if="errors.category" class="mt-1 text-xs text-red-500">{{ errors.category }}</p>
          </div>

          <!-- Title -->
          <div>
            <label for="title" class="mb-2 block text-sm font-bold text-[#10141f]"
              >Title <span class="text-red-500">*</span></label
            >
            <input
              type="text"
              id="title"
              v-model="form.title"
              placeholder="Brief summary of the issue"
              class="w-full rounded-lg border border-gray-300 px-4 py-2 transition-shadow focus:ring-2 focus:ring-[#25562e] focus:outline-none"
            />
            <p v-if="errors.title" class="mt-1 text-xs text-red-500">{{ errors.title }}</p>
          </div>

          <!-- Description -->
          <div>
            <label for="description" class="mb-2 block text-sm font-bold text-[#10141f]"
              >Description <span class="text-red-500">*</span></label
            >
            <textarea
              id="description"
              v-model="form.description"
              rows="4"
              placeholder="Please describe the issue in detail..."
              class="w-full rounded-lg border border-gray-300 px-4 py-2 transition-shadow focus:ring-2 focus:ring-[#25562e] focus:outline-none"
            ></textarea>
            <p v-if="errors.description" class="mt-1 text-xs text-red-500">
              {{ errors.description }}
            </p>
          </div>

          <!-- Priority -->
          <div>
            <label class="mb-2 block text-sm font-bold text-[#10141f]">Priority Level</label>
            <div class="flex items-start rounded-lg bg-blue-50 p-4">
              <ExclamationCircleIcon class="mr-2 h-6 w-6 flex-shrink-0 text-yellow-500" />
              <p class="text-sm text-blue-800">
                Most community reports are <strong>Medium</strong> or <strong>Low</strong> priority.
                High priority should be reserved for safety hazards.
              </p>
            </div>
            <div class="mt-3 flex gap-4">
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.priority"
                  value="low"
                  class="text-primary focus:ring-[#25562e]"
                />
                <span class="ml-2 text-[#10141f]">Low</span>
              </label>
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.priority"
                  value="medium"
                  class="text-primary focus:ring-[#25562e]"
                />
                <span class="ml-2 text-[#10141f]">Medium</span>
              </label>
              <label class="inline-flex items-center">
                <input
                  type="radio"
                  v-model="form.priority"
                  value="high"
                  class="text-primary focus:ring-[#25562e]"
                />
                <span class="ml-2 text-[#10141f]">High</span>
              </label>
            </div>
          </div>

          <!-- Image Upload -->
          <div>
            <label class="mb-2 block text-sm font-bold text-[#10141f]">Photo Evidence</label>
            <div
              class="relative cursor-pointer rounded-xl border-2 border-dashed border-gray-300 p-8 text-center transition-colors hover:bg-gray-50"
              @dragover.prevent
              @drop.prevent="handleFileDrop"
              @click="triggerFileInput"
            >
              <input
                type="file"
                ref="fileInput"
                class="hidden"
                accept="image/*"
                @change="handleFileSelect"
              />

              <div v-if="!form.image" class="flex flex-col items-center">
                <PhotoIcon class="mb-2 h-12 w-12 text-[#819796]" />
                <p class="text-sm font-semibold text-[#10141f]">Click to upload or drag and drop</p>
                <p class="mt-1 text-xs text-[#819796]">JPG, PNG, GIF up to 5MB</p>
              </div>

              <div v-else class="flex flex-col items-center">
                <p class="mb-2 flex items-center text-sm font-semibold text-green-600">
                  <CheckCircleIcon class="mr-1 h-5 w-5" />
                  {{ form.image.name }}
                </p>
                <button
                  type="button"
                  @click.stop="removeImage"
                  class="flex items-center text-sm text-[#cf573c] hover:underline"
                >
                  <TrashIcon class="mr-1 h-4 w-4" /> Remove image
                </button>
              </div>
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
              class="flex flex-1 items-center justify-center rounded-lg bg-gradient-to-r from-[#25562e] to-[#1a3d21] px-4 py-3 font-semibold text-white transition-shadow hover:shadow-lg disabled:cursor-not-allowed disabled:opacity-50"
            >
              <ArrowPathIcon v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
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
            <p class="flex items-center text-sm text-red-800">
              <ExclamationCircleIcon class="mr-2 h-5 w-5" />
              {{ error }}
            </p>
          </div>

          <!-- Success Message -->
          <div
            v-if="form.latitude && form.longitude"
            class="rounded-lg border border-green-200 bg-green-50 p-3"
          >
            <p class="flex items-center text-sm text-green-800">
              <CheckCircleIcon class="mr-2 h-5 w-5" />
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
import {
  ArrowLeftIcon,
  ExclamationCircleIcon,
  ArrowPathIcon,
  PhotoIcon,
  MapPinIcon,
  PaperAirplaneIcon,
  TrashIcon,
  CheckCircleIcon,
} from '@heroicons/vue/24/solid'

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
