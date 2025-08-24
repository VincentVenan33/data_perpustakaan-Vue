<script setup>
import { ref, computed, onMounted } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const books = ref([]);
const categories = ref([]);
const isLoading = ref(true);
const router = useRouter();

// state filter
const searchQuery = ref("");
const selectedCategory = ref("");
const selectedYear = ref("");
const selectedAvailability = ref("");

// ambil data buku
const fetchDataBooks = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/api/books");

    books.value = response.data.map((book) => ({
      ...book,
      category_name: book.category?.name || "-",
      user_name: book.user?.name || "-",
      published_year: book.published_at
        ? String(book.published_at).slice(0, 4)
        : "-"
    }));

    // ambil kategori unik
    categories.value = [
      ...new Set(books.value.map((book) => book.category_name).filter(Boolean))
    ];
  } catch (error) {
    console.error("Gagal fetch data buku:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  if (!token) {
    router.push({ name: "auth.login" });
  } else {
    fetchDataBooks();
  }
});

// hapus buku
const deleteBook = async (id) => {
  try {
    await api.delete(`/api/books/${id}`);
    fetchDataBooks();
  } catch (error) {
    console.error("Gagal hapus buku:", error);
  }
};

const confirmDelete = (id) => {
  Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data buku tidak bisa dikembalikan setelah dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then((result) => {
    if (result.isConfirmed) {
      deleteBook(id);
      Swal.fire("Terhapus!", "Buku berhasil dihapus.", "success");
    }
  });
};

// computed filter
const filteredBooks = computed(() => {
  return books.value.filter((book) => {
    const matchSearch = book.title
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());

    const matchCategory =
      selectedCategory.value === "" ||
      book.category_name === selectedCategory.value;

    const matchYear =
      selectedYear.value === "" || book.published_year === selectedYear.value;

    const matchAvailability =
      selectedAvailability.value === "" ||
      String(book.is_available) === selectedAvailability.value;

    return matchSearch && matchCategory && matchYear && matchAvailability;
  });
});
const exportExcel = async () => {
  try {
    const response = await api.get("/api/books/export", {
      responseType: "blob"
    });
    Swal.fire("Sukses", response.data.message, "success");
    // const blob = new Blob([response.data], {
    //   type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    // });
    // const link = document.createElement("a");
    // link.href = URL.createObjectURL(blob);
    // link.download = "members.xlsx";
    // link.click();
    // URL.revokeObjectURL(link.href);
  } catch (error) {
    console.error("Gagal Export:", error);
    Swal.fire("Error", "Gagal melakukan export data", "error");
  }
};

const importExcel = async (event) => {
  const file = event.target.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append("file", file);

  try {
    const response = await api.post("/api/books/import", formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });
    fetchDataBooks();
  } catch (error) {
    console.error("Gagal Import:", error);
    Swal.fire("Error", "Gagal melakukan import data", "error");
  }
};
</script>

<template>
  <div class="container mt-5 mb-5">
    <div class="row mb-3">
      <div class="col-md-9">
        <router-link
          :to="{ name: 'book.create' }"
          class="btn btn-md btn-success rounded shadow border-0 mb-3 me-2"
        >
          Tambah Buku
        </router-link>
        <button
          @click="exportExcel"
          class="btn btn-md btn-primary rounded shadow border-0 mb-3 me-2"
        >
          Export
        </button>

        <!-- Tombol Import -->
        <label class="btn btn-md btn-secondary rounded shadow border-0 mb-3">
          Import
          <input type="file" accept=".xlsx,.xls" @change="importExcel" hidden />
        </label>
      </div>
      <!-- Filter bar -->
      <div class="col-md-3 text-end">
        <input
          type="text"
          v-model="searchQuery"
          class="form-control"
          placeholder="Cari judul buku..."
        />
      </div>
    </div>
    <!-- Tabel -->
    <div class="card border-0 rounded shadow">
      <div class="card-body">
        <table class="table table-bordered">
          <thead class="bg-dark text-white">
            <tr>
              <th>Judul</th>
              <!-- Filter Kategori -->
              <th>
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">Kategori</div>
                  <div class="select-wrapper">
                    <select
                      v-model="selectedCategory"
                      class="form-select form-select-sm no-text"
                      style="padding-right: 1.5em"
                    >
                      <option value="">Semua Kategori</option>
                      <option v-for="cat in categories" :key="cat" :value="cat">
                        {{ cat }}
                      </option>
                    </select>

                    <i class="bi bi-filter-square"></i>
                  </div>
                </div>
              </th>

              <!-- Filter Tahun -->
              <th>
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">Tahun</div>
                  <div class="select-wrapper">
                    <select
                      v-model="selectedYear"
                      class="form-select form-select-sm no-text"
                      style="padding-right: 1.5em"
                    >
                      <option value="">Semua Tahun</option>
                      <option
                        v-for="year in [
                          ...new Set(books.map((b) => b.published_at))
                        ]"
                        :key="year"
                        :value="year"
                      >
                        {{ year }}
                      </option>
                    </select>
                    <i class="bi bi-filter-square"></i>
                  </div>
                </div>
              </th>

              <!-- Filter Ketersediaan -->
              <th>
                <div class="d-flex align-items-center">
                  <div class="flex-grow-1">Ketersediaan</div>
                  <div class="select-wrapper">
                    <select
                      v-model="selectedAvailability"
                      class="form-select form-select-sm no-text"
                      style="padding-right: 1.5em"
                    >
                      <option value="">Semua Ketersediaan</option>
                      <option
                        v-for="is_available in [
                          ...new Set(books.map((b) => b.is_available))
                        ]"
                        :key="is_available"
                        :value="is_available"
                      >
                        {{ is_available }}
                      </option>
                    </select>
                    <i class="bi bi-filter-square"></i>
                  </div>
                </div>
              </th>
              <th>Buku</th>
              <th>Aksi</th>
            </tr>
          </thead>

          <tbody>
            <tr v-if="isLoading">
              <td colspan="8" class="text-center">
                <div class="spinner-border text-primary" role="status">
                  <span class="visually-hidden">Loading...</span>
                </div>
              </td>
            </tr>
            <tr v-else-if="filteredBooks.length === 0">
              <td colspan="8" class="text-center">
                <div class="alert alert-danger mb-0">
                  Data Buku Belum Tersedia!
                </div>
              </td>
            </tr>
            <tr v-else v-for="(book, index) in filteredBooks" :key="index">
              <td>{{ book.title }}</td>
              <td>{{ book.category?.name }}</td>
              <td>{{ book.published_at }}</td>
              <td>{{ book.is_available }}</td>
              <td>
                <a
                  :href="`http://localhost:8000/storage/${book.file_path}`"
                  target="_blank"
                  v-if="book.file_path"
                  class="btn btn-outline-primary"
                  >Lihat Buku</a
                >
              </td>
              <td class="text-center">
                <router-link
                  :to="{ name: 'book.edit', params: { id: book.id } }"
                  class="btn btn-sm btn-primary me-2"
                  title="Edit"
                >
                  <i class="bi bi-pencil"></i>
                </router-link>
                <router-link
                  :to="{ name: 'book.detail', params: { id: book.id } }"
                  class="btn btn-sm btn-warning me-2"
                  title="Detail"
                >
                  <i class="bi bi-info-lg"></i>
                </router-link>
                <button
                  @click.prevent="confirmDelete(book.id)"
                  class="btn btn-sm btn-danger"
                  title="Delete"
                >
                  <i class="bi bi-trash"></i>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style>
.select-wrapper {
  position: relative;
  display: inline-block;
}

.select-wrapper select {
  appearance: none;
  -webkit-appearance: none;
  -moz-appearance: none;
  width: 30px;
  background-color: transparent;
  color: black;
  border: none;
  cursor: pointer;
  outline: none;
}

.select-wrapper i {
  position: absolute;
  right: 8px;
  top: 50%;
  transform: translateY(-50%);
  pointer-events: none;
  font-size: 20px;
}
</style>
