<template>
  <div class="animate-fade-in-up mx-auto max-w-4xl px-4 py-8">
    <!-- Header -->
    <div class="mb-8">
      <router-link
        to="/issues"
        class="text-text-light hover:text-primary group mb-4 inline-flex items-center gap-2 font-medium transition-colors"
      >
        <ArrowLeftIcon class="h-4 w-4 transition-transform group-hover:-translate-x-1" />
        Back to Issues
      </router-link>
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-text mb-2 text-3xl font-bold md:text-4xl">Report an Issue</h1>
          <p class="text-text-light text-lg">
            Help improve our community by reporting problems you see.
          </p>
        </div>
      </div>
    </div>

    <!-- Form Card -->
    <div class="shadow-soft overflow-hidden rounded-2xl border border-gray-100 bg-white">
      <form @submit.prevent="submitIssue" class="space-y-8 p-6 md:p-8">
        <!-- Section: Category -->
        <section>
          <h3 class="text-text mb-4 flex items-center gap-2 text-lg font-bold">
            <span
              class="bg-primary/10 text-primary flex h-6 w-6 items-center justify-center rounded-full text-sm"
              >1</span
            >
            What type of issue is this?
          </h3>
          <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
            <label v-for="cat in categories" :key="cat.value" class="group relative cursor-pointer">
              <input type="radio" v-model="form.category" :value="cat.value" class="peer sr-only" />
              <div
                class="peer-checked:border-primary peer-checked:bg-primary-light/20 group-hover:border-primary/50 rounded-xl border-2 border-gray-100 p-4 text-center transition-all duration-200 peer-checked:shadow-sm"
              >
                <!-- Icon placeholder (could be dynamic based on category) -->
                <div
                  class="text-primary mb-2 opacity-80 transition-transform duration-200 group-hover:scale-110"
                >
                  <FolderIcon class="mx-auto h-8 w-8" />
                </div>
                <span
                  class="text-text peer-checked:text-primary block text-sm font-semibold transition-colors"
                  >{{ cat.label }}</span
                >
              </div>
            </label>
          </div>
          <p v-if="errors.category" class="text-danger mt-2 flex items-center gap-1 text-sm">
            <ExclamationCircleIcon class="h-4 w-4" /> {{ errors.category }}
          </p>
        </section>

        <!-- Section: Details -->
        <section class="grid gap-8 md:grid-cols-2">
          <div class="space-y-6">
            <h3 class="text-text mb-4 flex items-center gap-2 text-lg font-bold">
              <span
                class="bg-primary/10 text-primary flex h-6 w-6 items-center justify-center rounded-full text-sm"
                >2</span
              >
              Describe the problem
            </h3>

            <!-- Title -->
            <div>
              <label for="title" class="text-text mb-1 block text-sm font-medium"
                >Title <span class="text-danger">*</span></label
              >
              <input
                type="text"
                id="title"
                v-model="form.title"
                placeholder="e.g., Pothole on Main St"
                class="focus:ring-primary/20 focus:border-primary w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2.5 transition-all outline-none focus:bg-white focus:ring-2"
              />
              <p v-if="errors.title" class="text-danger mt-1 text-xs">{{ errors.title }}</p>
            </div>

            <!-- Description -->
            <div>
              <label for="description" class="text-text mb-1 block text-sm font-medium"
                >Description <span class="text-danger">*</span></label
              >
              <textarea
                id="description"
                v-model="form.description"
                rows="4"
                placeholder="Please describe the issue in detail..."
                class="focus:ring-primary/20 focus:border-primary w-full resize-none rounded-lg border-gray-200 bg-gray-50 px-4 py-2.5 transition-all outline-none focus:bg-white focus:ring-2"
              ></textarea>
              <p v-if="errors.description" class="text-danger mt-1 text-xs">
                {{ errors.description }}
              </p>
            </div>

            <!-- Priority -->
            <div>
              <label class="text-text mb-2 block text-sm font-medium">Priority Level</label>
              <div class="flex gap-3">
                <label class="inline-flex cursor-pointer items-center">
                  <input type="radio" v-model="form.priority" value="low" class="peer sr-only" />
                  <span
                    class="text-text-light peer-checked:text-text rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium transition-colors peer-checked:border-gray-300 peer-checked:bg-gray-100"
                    >Low</span
                  >
                </label>
                <label class="inline-flex cursor-pointer items-center">
                  <input type="radio" v-model="form.priority" value="medium" class="peer sr-only" />
                  <span
                    class="text-text-light peer-checked:bg-warning-light peer-checked:text-warning-dark peer-checked:border-warning rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium transition-colors"
                    >Medium</span
                  >
                </label>
                <label class="inline-flex cursor-pointer items-center">
                  <input type="radio" v-model="form.priority" value="high" class="peer sr-only" />
                  <span
                    class="text-text-light peer-checked:bg-danger-light peer-checked:text-danger-dark peer-checked:border-danger rounded-lg border border-gray-200 px-3 py-1.5 text-sm font-medium transition-colors"
                    >High</span
                  >
                </label>
              </div>
              <p class="mt-2 inline-block rounded-lg bg-blue-50 p-2 text-xs text-blue-700">
                <ExclamationCircleIcon class="mr-1 inline h-3 w-3" />
                High priority is for immediate safety hazards.
              </p>
            </div>
          </div>

          <!-- Image Upload -->
          <div>
            <h3 class="text-text mb-4 flex items-center gap-2 text-lg font-bold">
              <span
                class="bg-primary/10 text-primary flex h-6 w-6 items-center justify-center rounded-full text-sm"
                >3</span
              >
              Add a photo
            </h3>
            <div
              class="hover:border-primary/50 group relative flex h-[calc(100%-3rem)] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-200 bg-gray-50 p-6 text-center transition-all hover:bg-white"
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

              <div v-if="!form.image" class="pointer-events-none flex flex-col items-center">
                <div
                  class="mb-3 rounded-full bg-white p-3 shadow-sm transition-transform group-hover:scale-110"
                >
                  <PhotoIcon class="text-primary h-8 w-8" />
                </div>
                <p class="text-text text-sm font-semibold">Click to upload or drag & drop</p>
                <p class="text-text-light mt-1 text-xs">JPG, PNG, GIF up to 5MB</p>
              </div>

              <div v-else class="flex w-full flex-col items-center">
                <div
                  v-if="imagePreview"
                  class="relative mb-3 h-48 w-full overflow-hidden rounded-lg shadow-sm"
                >
                  <img :src="imagePreview" class="h-full w-full object-cover" />
                  <button
                    @click.stop="removeImage"
                    class="text-danger absolute top-2 right-2 rounded-full bg-white/90 p-1.5 shadow-sm transition-colors hover:bg-white"
                  >
                    <TrashIcon class="h-4 w-4" />
                  </button>
                </div>
                <p class="text-success mb-2 flex items-center text-sm font-semibold">
                  <CheckCircleIcon class="mr-1 h-5 w-5" />
                  {{ form.image.name }}
                </p>
              </div>
            </div>
          </div>
        </section>

        <!-- Section: Map -->
        <section>
          <h3 class="text-text mb-4 flex items-center gap-2 text-lg font-bold">
            <span
              class="bg-primary/10 text-primary flex h-6 w-6 items-center justify-center rounded-full text-sm"
              >4</span
            >
            Location
          </h3>

          <!-- Location Text Input -->
          <div class="mb-4">
            <label for="location" class="text-text mb-1 block text-sm font-medium"
              >Address or Landmark <span class="text-danger">*</span></label
            >
            <input
              type="text"
              id="location"
              v-model="form.location"
              placeholder="e.g., Main St & 5th Ave, near City Hall"
              class="focus:ring-primary/20 focus:border-primary w-full rounded-lg border-gray-200 bg-gray-50 px-4 py-2.5 transition-all outline-none focus:bg-white focus:ring-2"
            />
            <p v-if="errors.location" class="text-danger mt-1 text-xs">{{ errors.location }}</p>
            <p class="text-text-light mt-1 text-xs">
              <MapPinIcon class="mr-1 inline h-3 w-3" />
              Provide a specific address or nearby landmark to help locate the issue
            </p>
          </div>

          <div class="overflow-hidden rounded-xl border border-gray-200 shadow-sm">
            <div id="map" class="z-0 h-80 w-full"></div>
          </div>
          <div class="mt-2 flex items-center justify-between text-sm">
            <p v-if="form.latitude" class="text-success flex items-center font-medium">
              <MapPinIcon class="mr-1 h-4 w-4" />
              Location selected: {{ form.latitude.toFixed(6) }}, {{ form.longitude.toFixed(6) }}
            </p>
            <p v-else class="text-text-light flex items-center">
              <ExclamationCircleIcon class="mr-1 h-4 w-4" />
              Tap on the map to pin the location
            </p>
          </div>
          <p v-if="error && error.includes('location')" class="text-danger mt-2 text-sm">
            {{ error }}
          </p>
        </section>

        <!-- Terms & Submit -->
        <div class="border-t border-gray-100 pt-6">
          <div class="mb-6 flex items-start gap-3">
            <input
              id="terms"
              v-model="form.agreeToTerms"
              type="checkbox"
              class="text-primary focus:ring-primary mt-1 rounded border-gray-300"
              required
            />
            <label for="terms" class="text-text-light cursor-pointer text-sm select-none">
              I confirm that this information is accurate and will help improve our community
            </label>
          </div>

          <!-- Submit Button -->
          <div class="flex gap-4">
            <button
              type="submit"
              :disabled="isSubmitting || !form.latitude || !form.longitude"
              class="btn-primary flex-1 py-3"
            >
              <ArrowPathIcon v-if="isSubmitting" class="mr-2 h-5 w-5 animate-spin" />
              {{ isSubmitting ? 'Submitting Report...' : 'Submit Report' }}
            </button>
            <router-link
              to="/dashboard"
              class="text-text flex-1 rounded-lg border border-gray-200 bg-white px-4 py-3 text-center font-semibold transition-colors hover:border-gray-300 hover:bg-gray-50"
            >
              Cancel
            </router-link>
          </div>

          <!-- Error Message -->
          <div
            v-if="error && !error.includes('location')"
            class="bg-danger-light/20 border-danger/20 mt-4 rounded-lg border p-4"
          >
            <p class="text-danger flex items-center text-sm">
              <ExclamationCircleIcon class="mr-2 h-5 w-5" />
              {{ error }}
            </p>
          </div>

          <!-- Success Message -->
          <div
            v-if="successMessage"
            class="bg-success-light/20 border-success/20 mt-4 rounded-lg border p-4"
          >
            <p class="text-success flex items-center text-sm">
              <CheckCircleIcon class="mr-2 h-5 w-5" />
              {{ successMessage }}
            </p>
          </div>
        </div>
      </form>
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
  TrashIcon,
  CheckCircleIcon,
  FolderIcon,
} from '@heroicons/vue/24/outline' // Switched to outline for cleaner look

const issuesStore = useIssuesStore()
const router = useRouter()

// Categories (Mock data for now, could be from API)
const categories = [
  { label: 'Roads', value: 'roads' },
  { label: 'Street Lights', value: 'street_lights' },
  { label: 'Trash', value: 'trash' },
  { label: 'Water & Drainage', value: 'water_drainage' },
  { label: 'Parks & Recreation', value: 'parks_recreation' },
  { label: 'Public Safety', value: 'public_safety' },
  { label: 'Graffiti & Vandalism', value: 'graffiti_vandalism' },
  { label: 'Noise', value: 'noise' },
  { label: 'Other', value: 'other' },
]

const form = ref({
  title: '',
  description: '',
  category: '',
  priority: 'medium',
  location: '',
  latitude: null,
  longitude: null,
  image: null,
  agreeToTerms: false,
})

const imagePreview = ref(null)
const isSubmitting = ref(false)
const error = ref('')
const successMessage = ref('')
const fileInput = ref(null)
let map = null
let marker = null

const triggerFileInput = () => {
  fileInput.value.click()
}

const handleFileSelect = (event) => {
  const file = event.target.files[0]
  if (file) {
    validateAndSetImage(file)
  }
}

const handleFileDrop = (event) => {
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

const errors = ref({})

const validateForm = () => {
  errors.value = {}
  let isValid = true

  if (!form.value.category) {
    errors.value.category = 'Please select a category'
    isValid = false
  }
  if (!form.value.title) {
    errors.value.title = 'Title is required'
    isValid = false
  }
  if (!form.value.description) {
    errors.value.description = 'Description is required'
    isValid = false
  }
  if (!form.value.location || form.value.location.trim() === '') {
    errors.value.location = 'Location address or landmark is required'
    isValid = false
  }

  return isValid
}

const initMap = () => {
  // Cotabato City coordinates
  const cotabatoCenterLat = 7.2167
  const cotabatoCenterLng = 124.25
  const cotabatoBounds = [
    [7.0967, 124.12], // Southwest
    [7.3367, 124.38], // Northeast
  ]

  map = L.map('map').setView([cotabatoCenterLat, cotabatoCenterLng], 13)

  map.setMaxBounds([
    [7.0767, 124.1],
    [7.3567, 124.4],
  ])

  L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '© OpenStreetMap contributors',
    maxZoom: 19,
    minZoom: 11,
  }).addTo(map)

  L.rectangle(cotabatoBounds, {
    color: '#047857', // Primary color
    weight: 2,
    opacity: 0.3,
    fillColor: '#10b981', // Accent color
    fillOpacity: 0.05,
  }).addTo(map)

  map.on('click', (event) => {
    const { lat, lng } = event.latlng

    if (
      lat >= cotabatoBounds[0][0] &&
      lat <= cotabatoBounds[1][0] &&
      lng >= cotabatoBounds[0][1] &&
      lng <= cotabatoBounds[1][1]
    ) {
      form.value.latitude = lat
      form.value.longitude = lng
      error.value = '' // Clear location error if any

      if (marker) {
        map.removeLayer(marker)
      }

      marker = L.marker([lat, lng])
        .addTo(map)
        .bindPopup(`<strong>Selected Location</strong>`)
        .openPopup()
    } else {
      error.value = 'Please select a location within Cotabato City'
    }
  })
}

const submitIssue = async () => {
  if (!validateForm()) return

  const token = localStorage.getItem('token')
  if (!token) {
    error.value = 'You must be logged in to report an issue.'
    setTimeout(() => router.push('/login'), 2000)
    return
  }

  if (!form.value.latitude || !form.value.longitude) {
    error.value = 'Please pin the location on the map.'
    return
  }

  // Re-check terms here just in case HTML5 validation fails
  if (!form.value.agreeToTerms) {
    error.value = 'You must agree to the terms.'
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
    formData.append('location', form.value.location)
    formData.append('latitude', form.value.latitude)
    formData.append('longitude', form.value.longitude)

    if (form.value.image) {
      formData.append('image', form.value.image)
    }

    await issuesStore.createIssue(formData)

    successMessage.value = 'Issue reported successfully! Redirecting...'

    setTimeout(() => {
      router.push('/dashboard')
    }, 2000)
  } catch (err) {
    if (err.toString().includes('401')) {
      error.value = 'Session expired. Please login again.'
      setTimeout(() => router.push('/login'), 2000)
    } else {
      error.value = err.message || 'Failed to report issue. Please try again.'
    }
  } finally {
    isSubmitting.value = false
  }
}

onMounted(() => {
  initMap()
})
</script>

<style scoped>
.animate-fade-in-up {
  animation: fadeInUp 0.5s ease-out forwards;
}

@keyframes fadeInUp {
  from {
    opacity: 0;
    transform: translateY(10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>
