<script setup>
const props = defineProps({
    href: String,
    text: String
})
</script>

<template>
    <a :href="href" target="_blank">{{text}}</a>
</template>
<style scoped>
a {
  padding: .25rem 1rem;
  font-size: 1rem;
  border-radius: .25rem;
  background: #006650;
  color: #fff;
}

a:hover {
  background: #00503f;
}
</style>