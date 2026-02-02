<script setup lang="ts">
import { computed } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const displayName = computed(() => authStore.displayName)

const goProfile = () => {
  router.push({ name: 'chat' })
}

const handleLogout = () => {
  authStore.logout()
  router.push({ name: 'home' })
}
</script>

<template>
  <header class="w-full sticky top-0 z-50 bg-gradient-to-r from-green-500 via-emerald-500 to-emerald-400 text-white shadow-lg backdrop-blur-sm bg-opacity-95">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-20 flex items-center justify-between">
      <div class="flex items-center gap-2 cursor-pointer group">
        <div class="w-8 h-8 bg-white/25 rounded-lg flex items-center justify-center group-hover:bg-white/35 transition">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 text-white">
            <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9.75L16.5 12l-2.25 2.25m-4.5 0L7.5 12l2.25-2.25M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <router-link :to="{ name: 'home' }" class="no-underline">
          <span class="font-bold text-lg tracking-wide group-hover:text-emerald-50 transition">LongkeChat</span>
        </router-link>
      </div>

      <div class="flex items-center gap-4">
        <div v-if="!isAuthenticated" class="flex items-center gap-2">
          <router-link :to="{ name: 'login' }">
            <button type="button" class="px-4 py-2 text-sm font-medium text-green-50 hover:text-white transition hover:bg-white/10 rounded-full">
              Log in
            </button>
          </router-link>
          <router-link :to="{ name: 'register' }">
            <button type="button" class="px-5 py-2 text-sm font-bold text-green-700 bg-white rounded-full shadow-md hover:bg-green-50 hover:shadow-lg transform hover:-translate-y-0.5 transition duration-200">
              Register
            </button>
          </router-link>
        </div>

        <div v-else class="flex items-center gap-3">
          <button
            type="button"
            class="px-4 py-2 text-sm font-semibold text-green-900 bg-white rounded-full shadow hover:bg-green-50 hover:shadow-md transition"
            @click="goProfile"
          >
            {{ displayName }}
          </button>
          <button
            type="button"
            class="px-4 py-2 text-sm font-semibold text-white border border-white/70 rounded-full hover:bg-white hover:text-emerald-600 transition"
            @click="handleLogout"
          >
            Logout
          </button>
        </div>
      </div>
    </div>
  </header>
</template>
