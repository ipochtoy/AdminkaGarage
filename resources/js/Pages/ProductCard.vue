<template>
  <div class="p-6 space-y-6">
    <header class="flex items-center justify-between">
      <div class="flex items-center gap-3">
        <h1 class="font-bold text-lg">{{ card.correlation_id }}</h1>
        <span class="text-xs px-2 py-1 rounded border" :class="card.status === 'processed' ? 'text-emerald-400 border-emerald-700' : 'text-amber-400 border-amber-700'">
          {{ card.status === 'processed' ? 'Обработано' : 'В работе' }}
        </span>
      </div>
      <div class="flex items-center gap-2">
        <button @click="save" class="bg-blue-600 hover:bg-blue-500 text-white px-4 py-1.5 rounded-md text-sm">Сохранить</button>
      </div>
    </header>

    <section class="bg-slate-800 rounded-xl border border-slate-700">
      <div class="px-4 py-3 border-b border-slate-700 flex justify-between items-center bg-slate-700/30">
        <h2 class="text-sm font-semibold text-slate-300">Фото <span class="text-slate-400">({{ photos.length }})</span></h2>
      </div>
      <div class="p-4">
        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 xl:grid-cols-8 gap-3">
          <div v-for="p in photos" :key="p.id" class="group relative aspect-[3/4] bg-slate-900 rounded-lg border border-slate-700 overflow-hidden">
            <img :src="p.url" class="w-full h-full object-cover" />
          </div>
        </div>
      </div>
    </section>

    <section class="bg-slate-800 rounded-xl border border-slate-700 p-4">
      <h2 class="text-sm font-semibold text-slate-300 mb-3">Данные</h2>
      <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <input v-model="form.title" type="text" placeholder="Название" class="bg-slate-900 border border-slate-700 rounded px-3 py-2" />
        <input v-model.number="form.price" type="number" step="0.01" min="0" placeholder="Цена" class="bg-slate-900 border border-slate-700 rounded px-3 py-2" />
        <input v-model="form.brand" type="text" placeholder="Бренд" class="bg-slate-900 border border-slate-700 rounded px-3 py-2" />
        <input v-model="form.category" type="text" placeholder="Категория" class="bg-slate-900 border border-slate-700 rounded px-3 py-2" />
      </div>
      <textarea v-model="form.description" rows="6" placeholder="Описание" class="mt-3 w-full bg-slate-900 border border-slate-700 rounded px-3 py-2"></textarea>
    </section>
  </div>
</template>

<script setup>
import { reactive } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

const page = usePage();
const props = defineProps({
  card: Object,
  photos: Array,
  barcodes: Array,
  ggLabels: Array,
});

const card = props.card;
const photos = props.photos ?? [];

const form = reactive({
  title: card.title ?? '',
  description: card.description ?? '',
  price: card.price ?? null,
  condition: card.condition ?? '',
  category: card.category ?? '',
  brand: card.brand ?? '',
  size: card.size ?? '',
  color: card.color ?? '',
  sku: card.sku ?? '',
  quantity: card.quantity ?? 1,
  ai_summary: card.ai_summary ?? '',
});

function save() {
  fetch(`/api/card/${card.id}/save`, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content ?? page.props.csrf_token,
      'Accept': 'application/json',
    },
    body: JSON.stringify(form),
  })
    .then(r => r.json())
    .then(() => {
      // noop
    });
}
</script>

<style scoped>
</style>
