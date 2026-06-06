// https://nuxt.com/docs/api/configuration/nuxt-config
export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  devtools: { enabled: false },
  ssr: true,

  srcDir: '.',

  modules: ['@pinia/nuxt'],

  css: [
    '~/assets/css/variables.css',
    '~/assets/css/base.css',
    '~/assets/css/components.css',
  ],

  runtimeConfig: {
    public: {
      apiBase: 'http://localhost:8000/api',
    },
  },

  routeRules: {
    '/admin/**': { ssr: false },
  },

  app: {
    baseURL: process.env.BASE_URL ?? '/',
    head: {
      charset: 'utf-8',
      viewport: 'width=device-width, initial-scale=1',
    },
  },
})
