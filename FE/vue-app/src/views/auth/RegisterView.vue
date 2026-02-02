<script setup lang="ts">
import { reactive } from 'vue'
import FormTitle from '@/components/forms/FormTitle.vue'
import FormLabel from '@/components/forms/FormLabel.vue'
import FormInput from '@/components/forms/FormInput.vue'
import FormError from '@/components/forms/FormError.vue'
import FormButton from '@/components/forms/FormButton.vue'
import SocialAuthButton from '@/components/forms/SocialAuthButton.vue'

const form = reactive({
  username: '',
  email: '',
  password: '',
  passwordConfirmation: '',
})

const errors = reactive<{ username?: string; email?: string; password?: string; passwordConfirmation?: string }>({})

const validate = () => {
  errors.username = form.username ? '' : 'Please enter a username.'
  errors.email = form.email && /.+@.+\..+/.test(form.email) ? '' : 'Please enter a valid email.'
  errors.password = form.password ? '' : 'Please enter a password.'
  errors.passwordConfirmation =
    form.passwordConfirmation === form.password ? '' : 'Passwords do not match.'
  return !errors.username && !errors.email && !errors.password && !errors.passwordConfirmation
}

const onSubmit = () => {
  if (!validate()) return
  // TODO: call register API. For now, just log values.
  console.log('submit register', { ...form })
}
</script>

<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="flex w-11/12 max-w-5xl h-[600px] bg-white rounded-lg shadow-lg overflow-hidden">
      <div
        class="w-1/2 bg-cover bg-center"
        style="background-image: url('/background-pictures/pexels-furkanelveren-27390908.jpg')"
      ></div>

      <form
        class="w-1/2 p-10 flex flex-col justify-center items-center bg-white/90 space-y-4"
        @submit.prevent="onSubmit"
      >
        <FormTitle>Sign up</FormTitle>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="username">
            <i class="fas fa-user mr-1"></i> Username:
          </FormLabel>
          <FormInput id="username" name="username" type="text" v-model="form.username" autocomplete="username" />
          <FormError :message="errors.username" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="email">
            <i class="fas fa-envelope mr-1"></i> Email:
          </FormLabel>
          <FormInput id="email" name="email" type="email" v-model="form.email" autocomplete="email" />
          <FormError :message="errors.email" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="password">
            <i class="fas fa-lock mr-1"></i> Password:
          </FormLabel>
          <FormInput
            id="password"
            name="password"
            type="password"
            v-model="form.password"
            autocomplete="new-password"
          />
          <FormError :message="errors.password" />
        </div>

        <div class="w-4/5 space-y-1">
          <FormLabel for-id="password_confirmation">
            <i class="fas fa-lock mr-1"></i> Re-enter Password:
          </FormLabel>
          <FormInput
            id="password_confirmation"
            name="password_confirmation"
            type="password"
            v-model="form.passwordConfirmation"
            autocomplete="new-password"
          />
          <FormError :message="errors.passwordConfirmation" />
        </div>

        <div class="w-full flex justify-center">
          <FormButton type="submit" class="w-4/5 mt-4 mb-2 h-12 text-base font-semibold">Sign up</FormButton>
        </div>

        <div class="flex gap-4">
          <SocialAuthButton url="/login/google" logo="/logos/google-logo-png-google-icon-logo-png-transparent-svg-vector-bie-supply-14.png" />
          <SocialAuthButton url="/login/facebook" logo="/logos/Facebook_Logo_2023.png" />
        </div>

        <div class="text-sm mt-4 text-gray-700">
          Already have an account?
          <RouterLink  :to="{name :'login'}" class="text-blue-500 hover:underline">Log in</RouterLink>
        </div>
      </form>
    </div>
  </div>
</template>
