<script setup>
import { ref } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";

const router = useRouter();

const name = ref("");
const description = ref("");
const errors = ref({});

const storeKategori = async () => {
  try {
    const formData = new FormData();
    formData.append("name", name.value);
    formData.append("description", description.value);

    await api.post("/api/categories", formData);

    router.push({ path: "/category/index" });
  } catch (error) {
    errors.value = error.response?.data?.errors || {};
  }
};
</script>

<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <form @submit.prevent="storeKategori()">
              <div class="mb-3">
                <label class="form-label fw-bold">Kategori</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="name"
                  placeholder="Masukkan kategori"
                />
                <div v-if="errors.name" class="alert alert-danger mt-2">
                  <span>{{ errors.name[0] }}</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <textarea
                  class="form-control"
                  v-model="description"
                  rows="4"
                  placeholder="Masukkan deskripsi"
                ></textarea>
                <div v-if="errors.description" class="alert alert-danger mt-2">
                  <span>{{ errors.description[0] }}</span>
                </div>
              </div>

              <button
                type="submit"
                class="btn btn-primary rounded-sm shadow border-0"
              >
                Simpan Kategori
              </button>
              <button
                class="btn btn-secondary rounded-sm shadow border-0 ml-2"
                @click="router.push({ path: '/category/index' })"
              >
                Kembali
              </button>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
