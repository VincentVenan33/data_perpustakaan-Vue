<script setup>
import { ref, onMounted, computed } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const users = ref([]);
const isLoading = ref(true);
const router = useRouter();

const searchQuery = ref("");
const selectedRole = ref("");

// Ambil daftar role unik dari users untuk dropdown filter
const roles = computed(() => {
  const roleSet = new Set(users.value.map((user) => user.role));
  return Array.from(roleSet);
});

const fetchDataUsers = async () => {
  isLoading.value = true;
  try {
    const response = await api.get("/api/users");
    users.value = response.data;
  } catch (error) {
    console.error("Gagal fetch user:", error);
  } finally {
    isLoading.value = false;
  }
};

onMounted(() => {
  const token = localStorage.getItem("token");
  const role = localStorage.getItem("userRole") || "User "; // Ambil role dari localStorage
  if (!token) {
    router.push({ name: "auth.login" });
    return;
  }
  if (role !== "Administrator") {
    Swal.fire({
      icon: "error",
      title: "Akses Ditolak",
      text: "Halaman ini hanya untuk Administrator!",
      confirmButtonText: "Kembali"
    }).then(() => {
      router.push({ name: "dashboard.index" });
    });
    return;
  }
  fetchDataUsers();
});

const deleteUser = async (id) => {
  try {
    await api.delete(`/api/users/${id}`);
    fetchDataUsers();
  } catch (error) {
    console.error("Gagal hapus user:", error);
  }
};
const confirmDelete = (id) => {
  Swal.fire({
    title: "Yakin ingin menghapus?",
    text: "Data tidak bisa dikembalikan setelah dihapus!",
    icon: "warning",
    showCancelButton: true,
    confirmButtonColor: "#d33",
    cancelButtonColor: "#3085d6",
    confirmButtonText: "Ya, hapus!",
    cancelButtonText: "Batal"
  }).then((result) => {
    if (result.isConfirmed) {
      deleteUser(id);
      Swal.fire("Terhapus!", "Data berhasil dihapus.", "success");
    }
  });
};

// Computed filtered users
const filteredUsers = computed(() => {
  return users.value.filter((user) => {
    const matchesSearch =
      user.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
      user.email.toLowerCase().includes(searchQuery.value.toLowerCase());

    const matchesRole =
      selectedRole.value === "" || user.role === selectedRole.value;

    return matchesSearch && matchesRole;
  });
});
</script>

<template>
  <div class="container mt-5 mb-5">
    <div class="row mb-3">
      <div class="col-md-9">
        <router-link
          :to="{ name: 'user.create' }"
          class="btn btn-md btn-success rounded shadow border-0"
        >
          Tambah User
        </router-link>
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
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <table class="table table-bordered">
              <thead class="bg-dark text-white">
                <tr>
                  <th>Name</th>
                  <th>Email</th>
                  <th>
                    <div class="d-flex align-items-center">
                      <div class="flex-grow-1">Role</div>
                      <div class="select-wrapper">
                        <select
                          v-model="selectedRole"
                          class="form-select form-select-sm no-text"
                          style="padding-right: 1.5em"
                        >
                          <option value="">Semua Role</option>
                          <option
                            v-for="role in roles"
                            :key="role"
                            :value="role"
                          >
                            {{ role }}
                          </option>
                        </select>

                        <i class="bi bi-filter-square"></i>
                      </div>
                    </div>
                  </th>
                  <th style="width: 15%">Actions</th>
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
                <tr v-else-if="filteredUsers.length === 0">
                  <td colspan="4" class="text-center">Tidak ada data user</td>
                </tr>
                <tr v-else v-for="(user, index) in filteredUsers" :key="index">
                  <td>{{ user.name }}</td>
                  <td>{{ user.email }}</td>
                  <td>{{ user.role }}</td>
                  <td class="text-center">
                    <router-link
                      :to="{ name: 'user.edit', params: { id: user.id } }"
                      class="btn btn-sm btn-primary rounded-sm shadow border-0 me-2"
                      title="Edit"
                      ><i class="bi bi-pencil"></i
                    ></router-link>
                    <router-link
                      :to="{ name: 'user.detail', params: { id: user.id } }"
                      class="btn btn-sm btn-warning rounded-sm shadow border-0 me-2"
                      title="Detail"
                      ><i class="bi bi-info-lg"></i
                    ></router-link>
                    <button
                      @click.prevent="confirmDelete(user.id)"
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