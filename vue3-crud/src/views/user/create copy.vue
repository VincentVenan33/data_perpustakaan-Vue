<script setup>
import { onMounted, ref } from "vue";
import { useRouter } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();

const name = ref("");
const email = ref("");
const password = ref("");
const errors = ref([]);

// Store User
const storeUser = async () => {
  try {
    const response = await api.post("/api/users", {
      name: name.value,
      email: email.value,
      password: password.value
    });

    if (response.status === 201 || response.status === 200) {
      router.push({ path: "/users/index" }); // arahkan ke halaman user list
    }
  } catch (error) {
    if (error.response && error.response.data) {
      errors.value = error.response.data;

      // Kalau error tidak berbentuk field, misal dari Laravel pakai `message`
      if (error.response.data.message) {
        errors.value.message = error.response.data.message;
      }
    }
  }
};
onMounted(() => {
  const token = localStorage.getItem("token");
  const role = localStorage.getItem("userRole") || "User "; // Ambil role dari localStorage
  if (!token) {
    router.push({ name: "auth.login" });
    return; // Hentikan eksekusi lebih lanjut
  }
  // Periksa role pengguna
  if (role !== "Administrator") {
    Swal.fire({
      icon: "error",
      title: "Akses Ditolak",
      text: "Halaman ini hanya untuk Administrator!",
      confirmButtonText: "Kembali"
    }).then(() => {
      router.push({ name: "dashboard.index" });
    });
    return; // Hentikan eksekusi lebih lanjut
  }
  console.log("Akses Administrator, lanjut fetch...");
  fetchDataUsers();
});
</script>

<template>
  <div class="container mt-5">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <form @submit.prevent="storeUser">
              <div class="mb-3">
                <label class="form-label fw-bold">Nama</label>
                <input
                  type="text"
                  class="form-control"
                  v-model="name"
                  placeholder="Nama lengkap"
                />
                <div v-if="errors.name" class="alert alert-danger mt-2">
                  <span>{{ errors.name[0] }}</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Email</label>
                <input
                  type="email"
                  class="form-control"
                  v-model="email"
                  placeholder="Email aktif"
                />
                <div v-if="errors.email" class="alert alert-danger mt-2">
                  <span>{{ errors.email[0] }}</span>
                </div>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Password</label>
                <input
                  type="password"
                  class="form-control"
                  v-model="password"
                  placeholder="Minimal 5 karakter"
                />
                <div v-if="errors.password" class="alert alert-danger mt-2">
                  <span>{{ errors.password[0] }}</span>
                </div>
              </div>
              <button
                type="submit"
                class="btn btn-md btn-primary rounded-sm shadow border-0"
              >
                Simpan
              </button>

              <button
                type="button"
                @click="
                  () => {
                    router.push({ name: 'user.index' });
                  }
                "
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
