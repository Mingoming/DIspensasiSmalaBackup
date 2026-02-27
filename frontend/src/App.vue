<script setup>
import { RouterView } from 'vue-router'
</script>

<template>
  <RouterView />
</template>

<style>
/* Global styles untuk print */
@media print {
  nav, .no-print, button {
    display: none !important;
  }
  
  body {
    background: white !important;
  }
}
</style>
