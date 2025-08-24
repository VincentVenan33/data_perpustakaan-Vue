<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";

const router = useRouter();
const route = useRoute();

const title = ref("");
const category_id = ref("");
const categories = ref([]);
const published_at = ref("");
const is_available = ref("");
const details = ref("");
const file = ref(null);
const errors = ref({});
const isLoading = ref(true);

// Ambil data kategori untuk dropdown
onMounted(async () => {
  try {
    const catResponse = await api.get("/api/categories");
    categories.value = catResponse.data.data || catResponse.data;
  } catch (error) {
    console.error("Gagal ambil kategori", error);
  }
  isLoading.value = true; // mulai loading
  try {
    const res = await api.get(`/api/books/${route.params.id}`);
    const book = res.data.data || res.data;

    title.value = book.title;
    category_id.value = book.category_id;
    published_at.value = book.published_at || "";
    is_available.value = book.is_available || "";
    details.value = book.details || "";
  } catch (error) {
    console.error("Gagal ambil data buku", error);
  } finally {
    isLoading.value = false; // selesai loading
  }
});

const handleFileChange = (e) => {
  file.value = e.target.files[0];
};

const updateBook = async () => {
  errors.value = {};

  const formData = new FormData();
  formData.append("title", title.value);
  formData.append("category_id", category_id.value);
  formData.append("published_at", published_at.value);
  formData.append("is_available", is_available.value);
  formData.append("details", details.value);
  formData.append("_method", "PATCH"); // Laravel method spoofing
  if (file.value) formData.append("file", file.value);

  try {
    await api.post(`/api/books/${route.params.id}`, formData, {
      headers: {
        "Content-Type": "multipart/form-data"
      }
    });
    router.push({ path: "/book/index" });
  } catch (error) {
    if (error.response && error.response.data.errors) {
      errors.value = error.response.data.errors;
    } else {
      console.error(error);
    }
  }
};
</script>

<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <form v-else @submit.prevent="updateBook">
              
              <div class="mb-3">
                <label class="form-label fw-bold">Judul Buku</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="title"
                  placeholder="Judul Buku"
                />
                <div v-if="errors.title" class="alert alert-danger mt-2">
                  {{ errors.title[0] }}
                </div>
              </div>

              <!-- Category Dropdown -->
              <div class="mb-3">
                <label class="form-label fw-bold">Kategori</label>
                <select class="form-select" v-model="category_id">
                  <option value="" disabled>Pilih kategori</option>
                  <option
                    v-for="cat in categories"
                    :key="cat.id"
                    :value="cat.id"
                  >
                    {{ cat.name || cat.kategori }}
                  </option>
                </select>
                <div v-if="errors.category_id" class="alert alert-danger mt-2">
                  {{ errors.category_id[0] }}
                </div>
              </div>

              <!-- Published At -->
              <div class="mb-3">
                <label class="form-label fw-bold">Tahun Publikasi</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="published_at"
                  placeholder="YYYY"
                />
                <div v-if="errors.published_at" class="alert alert-danger mt-2">
                  {{ errors.published_at[0] }}
                </div>
              </div>

              <!-- Is Available (jumlah) -->
              <div class="mb-3">
                <label class="form-label fw-bold"
                  >Jumlah Ketersediaan Buku</label
                >
                <input
                  type="number"
                  class="form-control"
                  v-model="is_available"
                />
                <div v-if="errors.is_available" class="alert alert-danger mt-2">
                  {{ errors.is_available[0] }}
                </div>
              </div>

              <!-- Details -->
              <div class="mb-3">
                <label class="form-label fw-bold">Detail</label>
                <textarea
                  class="form-control"
                  rows="5"
                  v-model="details"
                  placeholder="Deskripsi atau detail buku"
                ></textarea>
                <div v-if="errors.details" class="alert alert-danger mt-2">
                  {{ errors.details[0] }}
                </div>
              </div>

              <!-- Upload File PDF -->
              <div class="mb-3">
                <label class="form-label fw-bold"
                  >Upload File PDF (opsional)</label
                >
                <input
                  type="file"
                  class="form-control"
                  accept=".pdf"
                  @change="handleFileChange"
                />
                <div v-if="errors.file" class="alert alert-danger mt-2">
                  {{ errors.file[0] }}
                </div>
              </div>

              <button type="submit" class="btn btn-primary rounded-sm shadow border-0">Update Buku</button>
               <button
                class="btn btn-secondary ml-2 rounded-sm shadow border-0"
                @click="router.push({ path: '/book/index' })"
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
