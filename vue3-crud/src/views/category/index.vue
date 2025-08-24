<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const categories = ref([]);
const isLoading = ref(true);
const router = useRouter();

const searchQuery = ref("");

const fetchDataCategories = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/api/categories");
    categories.value = response.data;
  } catch (error) {
    console.error("Gagal fetch data kategori:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  if (!token) {
    router.push({ name: "auth.login" });
  } else {
    fetchDataCategories();
  }
});

const deleteCategory = async (id) => {
  try {
    await api.delete(`/api/categories/${id}`);
    fetchDataCategories();
  } catch (error) {
    console.error("Gagal hapus kategori:", error);
  }
};

const confirmDelete = (id) => {
  Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data kategori tidak bisa dikembalikan setelah dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then((result) => {
    if (result.isConfirmed) {
      deleteCategory(id);
      Swal.fire("Terhapus!", "Kategori berhasil dihapus.", "success");
    }
  });
};
const filteredCategories = computed(() => {
  return categories.value.filter((category) => {
    // filter search judul
    const matchSearch = category.name
      .toLowerCase()
      .includes(searchQuery.value.toLowerCase());

    return matchSearch;
  });
});
const exportExcel = async () => {
  try {
    const response = await api.get("/api/categories/export", {
      responseType: "blob"
    });
    Swal.fire("Sukses", response.data.message, "success");
    // const blob = new Blob([response.data], {
    //   type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
    // });
    // const link = document.createElement("a");
    // link.href = URL.createObjectURL(blob);
    // link.download = "categories.xlsx";
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
    await api.post("/api/categories/import", formData, {
      headers: { "Content-Type": "multipart/form-data" }
    });
    Swal.fire("Berhasil", "Import data kategori berhasil!", "success");
    fetchDataCategories();
  } catch (error) {
    console.error("Gagal Import:", error);
    Swal.fire("Error", "Gagal melakukan import data", "error");
  }
};
</script>

<template>
  <div class="container mt-5 mb-5">
    <div class="row">
      <div class="col-md-12">
        <div class="row mb-3">
          <div class="col-md-9">
            <router-link
              :to="{ name: 'category.create' }"
              class="btn btn-md btn-success rounded shadow border-0 mb-3 me-2"
            >
              Tambah Kategori
            </router-link>

            <!-- Tombol Export -->
            <button
              @click="exportExcel"
              class="btn btn-md btn-primary rounded shadow border-0 mb-3 me-2"
            >
              Export
            </button>

            <!-- Tombol Import -->
            <label
              class="btn btn-md btn-secondary rounded shadow border-0 mb-3"
            >
              Import
              <input
                type="file"
                accept=".xlsx,.xls"
                @change="importExcel"
                hidden
              />
            </label>
          </div>

          <!-- Filter bar -->
          <div class="col-md-3 text-end">
            <input
              type="text"
              v-model="searchQuery"
              class="form-control"
              placeholder="Cari kategori..."
            />
          </div>
        </div>
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <table class="table table-bordered">
              <thead class="bg-dark text-white">
                <tr>
                  <th scope="col">Nama</th>
                  <th scope="col">Aksi</th>
                </tr>
              </thead>
              <tbody>
                <tr v-if="isLoading">
                  <td colspan="4" class="text-center">
                    <div class="spinner-border text-primary" role="status">
                      <span class="visually-hidden">Loading...</span>
                    </div>
                  </td>
                </tr>
                <tr v-else-if="filteredCategories.length == 0">
                  <td colspan="4" class="text-center">
                    <div class="alert alert-danger mb-0">
                      Data Kategori Belum Tersedia!
                    </div>
                  </td>
                </tr>
                <tr
                  v-else
                  v-for="(category, index) in filteredCategories"
                  :key="index"
                >
                  <td>{{ category.name }}</td>
                  <td class="text-center">
                    <router-link
                      :to="{
                        name: 'category.detail',
                        params: { id: category.id }
                      }"
                      class="btn btn-sm btn-warning rounded-sm shadow border-0 me-2"
                      title="Detail"
                      ><i class="bi bi-info-lg"></i
                    ></router-link>
                    <button
                      @click.prevent="confirmDelete(category.id)"
                      class="btn btn-sm btn-danger rounded-sm shadow border-0"
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
    </div>
  </div>
</template>
