<script setup>
import { ref, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";

const router = useRouter();

const title = ref("");
const category_id = ref("");
const categories = ref([]);
const published_at = ref("");
const is_available = ref(1); // jumlah ketersediaan buku (number)
const details = ref("");
const file = ref(null);
const errors = ref({});

// Ambil data kategori saat mounted
onMounted(async () => {
  try {
    const response = await api.get("/api/categories");
    categories.value = response.data || response.data; // sesuaikan struktur API
  } catch (error) {
    console.error("Gagal ambil kategori", error);
  }
});

const handleFileChange = (e) => {
  file.value = e.target.files[0];
};

const storeBook = async () => {
  errors.value = {}; // reset error

  const formData = new FormData();
  formData.append("title", title.value);
  formData.append("category_id", category_id.value);
  formData.append("published_at", published_at.value);
  formData.append("is_available", is_available.value);
  formData.append("details", details.value);
  if (file.value) formData.append("file", file.value);

  try {
    await api.post("/api/books", formData, {
      headers: { "Content-Type": "multipart/form-data" }
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
            <form @submit.prevent="storeBook">
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

              <!-- Published Year -->
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

              <!-- Availability (jumlah buku) -->
              <div class="mb-3">
                <label class="form-label fw-bold"
                  >Jumlah Ketersediaan Buku</label
                >
                <input
                  type="number"
                  min="0"
                  class="form-control"
                  v-model.number="is_available"
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
                  v-model="details"
                  rows="5"
                  placeholder="Deskripsi atau detail buku"
                ></textarea>
                <div v-if="errors.details" class="alert alert-danger mt-2">
                  {{ errors.details[0] }}
                </div>
              </div>

              <!-- Upload PDF -->
              <div class="mb-3">
                <label class="form-label fw-bold">
                  Upload File PDF (100KB - 500KB)
                </label>
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

              <button type="submit" class="btn btn-primary rounded-sm shadow border-0">Simpan Buku</button>
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
