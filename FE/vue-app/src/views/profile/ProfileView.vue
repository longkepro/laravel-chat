<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import { useRouter } from 'vue-router'
import { useToast } from 'vue-toastification'
import api from '@/api/api'
import { useAuthStore } from '@/stores/auth'

const router = useRouter()
const toast = useToast()
const authStore = useAuthStore()

const isAuthenticated = computed(() => authStore.isAuthenticated)
const user = computed(() => authStore.user)

const displayName = ref('')
const selectedAvatarFile = ref<File | null>(null)
const avatarPreviewUrl = ref<string | null>(null)

const resolvedAvatarUrl = computed(() => {
  const raw = user.value?.avatar
  if (!raw) return null

  // If BE returns relative path (e.g. /storage/...), prefix API base.
  if (raw.startsWith('/')) {
    const base = import.meta.env.VITE_API_BASE_URL?.replace(/\/$/, '')
    return `${base}${raw}`
  }

  return raw
})

function onPickAvatar(e: Event) {
  const input = e.target as HTMLInputElement
  const file = input.files?.[0] ?? null

  selectedAvatarFile.value = file

  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value)
    avatarPreviewUrl.value = null
  }

  if (file) {
    avatarPreviewUrl.value = URL.createObjectURL(file)
  }
}

async function saveProfile() {
  if (!isAuthenticated.value) {
    router.push({ name: 'login' })
    return
  }

  try {
    // Ensure XSRF cookie exists for Sanctum stateful POST requests
    await api.getCsrfCookie()
    const response = await api.editProfile({
      profile_name: displayName.value.trim() || undefined,
      avatar: selectedAvatarFile.value,
    })

    if ('error' in response.data) {
      toast.error(response.data.error)
      return
    }

    await authStore.fetchUser()

    selectedAvatarFile.value = null
    if (avatarPreviewUrl.value) {
      URL.revokeObjectURL(avatarPreviewUrl.value)
      avatarPreviewUrl.value = null
    }

    toast.success('Cập nhật hồ sơ thành công!')
  } catch (e) {
    console.error(e)
    const maybeAny = e as any
    const message = maybeAny?.response?.data?.error ?? 'Cập nhật hồ sơ thất bại.'
    toast.error(message)
  }
}

onMounted(async () => {
  await authStore.fetchUserIfNeeded()

  if (!isAuthenticated.value) {
    router.push({ name: 'login' })
    return
  }

  displayName.value = user.value?.name ?? ''
})

onBeforeUnmount(() => {
  if (avatarPreviewUrl.value) {
    URL.revokeObjectURL(avatarPreviewUrl.value)
  }
})
</script>

<template>
  <main class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="bg-white rounded-2xl shadow-sm border border-stone-100 p-6">
      <h1 class="text-xl font-semibold text-stone-900">Trang cá nhân</h1>
      <p class="mt-1 text-sm text-stone-600">Xem và cập nhật tên hiển thị, avatar.</p>

      <div class="mt-6 flex flex-col sm:flex-row gap-6">
        <div class="flex-shrink-0">
          <div class="w-24 h-24 rounded-2xl overflow-hidden bg-stone-100 border border-stone-200">
            <img
              v-if="avatarPreviewUrl || resolvedAvatarUrl"
              :src="avatarPreviewUrl ?? resolvedAvatarUrl ?? ''"
              class="w-full h-full object-cover"
              alt=""
            />
            <div v-else class="w-full h-full flex items-center justify-center text-stone-400 text-sm">No avatar</div>
          </div>

          <label class="mt-3 inline-flex items-center justify-center px-4 py-2 text-sm font-semibold text-stone-700 bg-stone-50 border border-stone-200 rounded-xl cursor-pointer hover:bg-stone-100 transition">
            Chọn avatar
            <input type="file" class="hidden" accept="image/*" @change="onPickAvatar" />
          </label>
        </div>

        <div class="flex-1 space-y-4">
          <div>
            <label class="block text-sm font-medium text-stone-700">Username</label>
            <div class="mt-1 px-3 py-2 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-sm">
              {{ user?.username ?? '' }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-stone-700">Email</label>
            <div class="mt-1 px-3 py-2 rounded-xl bg-stone-50 border border-stone-200 text-stone-700 text-sm">
              {{ user?.email ?? '' }}
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-stone-700">Tên hiển thị</label>
            <input
              v-model="displayName"
              type="text"
              class="mt-1 w-full px-3 py-2 rounded-xl border border-stone-200 focus:outline-none focus:ring-2 focus:ring-emerald-200 focus:border-emerald-300"
              placeholder="Nhập tên hiển thị"
            />
          </div>

          <div class="pt-2">
            <button
              type="button"
              class="inline-flex items-center justify-center px-5 py-2.5 text-sm font-bold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 transition"
              @click="saveProfile"
            >
              Lưu thay đổi
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>
</template>
