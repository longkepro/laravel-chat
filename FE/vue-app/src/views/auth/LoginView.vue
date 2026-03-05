<script setup lang="ts">
import { reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import FormTitle from '@/components/forms/FormTitle.vue'
import FormLabel from '@/components/forms/FormLabel.vue'
import FormInput from '@/components/forms/FormInput.vue'
import FormError from '@/components/forms/FormError.vue'
import FormButton from '@/components/forms/FormButton.vue'
import SocialAuthButton from '@/components/forms/SocialAuthButton.vue'
import { RouterLink } from 'vue-router'
import { isAxiosError } from 'axios'
import { useToast } from 'vue-toastification'

const form = reactive({
  username: '',
  password: '',
})

const router = useRouter()
const AuthStore = useAuthStore()
const toast = useToast()

const errors = reactive<{ username?: string; password?: string }>({})

const validate = () => {
  errors.username = form.username ? '' : 'Please enter your username.'
  errors.password = form.password ? '' : 'Please enter your password.'
  return !errors.username && !errors.password
}

async function onSubmit (){
  if (validate()){

    try{
      console.log('Submitting login form with:', form)
      console.log('API endpoint:', import.meta.env.VITE_API_BASE_URL)
      const payload = {
        username: form.username,
        password: form.password,
      }
      await AuthStore.login(payload)
      if(AuthStore.user){
        toast.success('Đăng nhập thành công!')
        router.push({ name: 'home' })
      }

    } catch (error: unknown) {
      if (isAxiosError(error) && error.response?.status === 422) {
        const errorData = error.response.data as any;
        const errorMessage = errorData?.errors?.username?.[0] || errorData?.message || 'Đăng nhập thất bại.';
        errors.password = errorMessage;
        toast.error(`Đăng nhập thất bại: ${errorMessage}`);
      } else {
        console.error('Login error:', error)
        toast.error('Đăng nhập thất bại. Vui lòng thử lại.')
      }
    }
  }
  else{
    toast.error('Vui lòng điền đầy đủ thông tin.')
  }
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="flex w-11/12 max-w-5xl h-[600px] bg-white rounded-lg shadow-lg overflow-hidden">
      <div
        class="w-1/2 bg-cover bg-top"
        style="background-image: url('/background-pictures/pexels-furkanelveren-27390908.jpg')"
      ></div>

      <form
        class="w-1/2 p-10 flex flex-col justify-center items-center bg-white/90 space-y-6"
        @submit.prevent="onSubmit"
      >
        <FormTitle>Sign In</FormTitle>
 
        <div class="w-4/5 space-y-1">
          <FormLabel for-id="username">
            <i class="fas fa-user mr-2"></i>User name:
          </FormLabel>
          <FormInput
            id="username"
            name="username"
            type="text"
            v-model="form.username"
            autocomplete="username"
          />
          <FormError :message="errors.username" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="password">
            <i class="fas fa-lock mr-2"></i>Password:
          </FormLabel>
          <FormInput
            id="password"
            name="password"
            type="password"
            v-model="form.password"
            autocomplete="current-password"
          />
          <FormError :message="errors.password" />
        </div>

        <FormButton type="submit">Log In</FormButton>
        <div class="flex gap-4">
          <SocialAuthButton auth-provider="google" logo="../logos/google-logo-png-google-icon-logo-png-transparent-svg-vector-bie-supply-14.png" />
          <SocialAuthButton auth-provider="facebook" logo="../logos/Facebook_Logo_2023.png" />
        </div>
        <p class="text-sm mt-4">
          Don't have an account yet?
          <RouterLink :to="{name :'register'}" class="text-blue-500 hover:underline">Sign Up</RouterLink>
        </p>
      </form>
    </div>
  </div>
</template>