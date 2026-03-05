<script setup lang="ts">
import { computed, useAttrs } from 'vue'
import { useAuthStore } from '@/stores/auth';
import { f } from 'vue-router/dist/router-CWoNjPRp.mjs';

const props = defineProps<{
  authProvider: string
  logo: string
  alt?: string
}>()

const attrs = useAttrs()
const altText = computed(() => props.alt ?? 'Social login')

function Oauthenticate(provider: string) {
  const AuthStore = useAuthStore();
  AuthStore.Oauth(provider);
}
</script>

<template>
  <button
    type="button"
    @click= "Oauthenticate(props.authProvider)"
    v-bind="attrs"
    :class="['flex items-center px-4 py-2 rounded-lg shadow-sm bg-white hover:bg-gray-100', attrs.class]"
  >
    <img :src="props.logo" :alt="altText" class="h-5 w-5 mr-3" />
    <slot />
</button>
</template>
