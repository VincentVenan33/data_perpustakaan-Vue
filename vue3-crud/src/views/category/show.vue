<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();
const route = useRoute();

const kategori = ref("");
const deskripsi = ref("");
const isLoading = ref(true);
const audits = ref([]);
const usersMap = ref({}); // Map user_id -> nama

onMounted(async () => {
  try {
    // Ambil detail kategori
    const resDetail = await api.get(`/api/categories/${route.params.id}`);
    kategori.value =
      resDetail.data.data.kategori || resDetail.data.data.name || "";
    deskripsi.value =
      resDetail.data.data.deskripsi || resDetail.data.data.description || "";

    // Ambil audit trail kategori
    const resAudits = await api.get(
      `/api/categories/${route.params.id}/audits`
    );
    audits.value = resAudits.data || [];

    // Ambil semua user_id unik dari audit
    const userIds = [
      ...new Set(audits.value.map((a) => a.user_id).filter(Boolean))
    ];

    if (userIds.length > 0) {
      const resUsers = await api.get(`/api/users`, {
        params: { ids: userIds }
      });
      // Bentuk map user_id -> nama
      resUsers.data.forEach((user) => {
        usersMap.value[user.id] = user.name;
      });
    }
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Gagal mengambil data",
      text: "Kategori tidak ditemukan atau terjadi kesalahan."
    }).then(() => {
      router.push({ path: "/category/index" });
    });
  } finally {
    isLoading.value = false;
  }
});

function formatTanggal(tanggal) {
  return new Date(tanggal).toLocaleDateString("id-ID", {
    day: "numeric",
    month: "long",
    year: "numeric"
  });
}

function mapEvent(event) {
  const map = {
    created: "Menambah",
    updated: "Mengubah",
    deleted: "Menghapus"
  };
  return map[event] || event;
}

function getUserName(audit) {
  return usersMap.value[audit.user_id] || "-";
}

function formatChanges(oldValues, newValues) {
  let changes = "";
  for (const field in newValues) {
    if (field === "id") continue;

    let label = field;
    if (field === "name") label = "Nama";
    if (field === "description") label = "Deskripsi";

    const oldVal = oldValues[field] || "-";
    const newVal = newValues[field] || "-";

    changes += `${label}: ${oldVal} → ${newVal}\n`;
  }
  return changes.trim();
}
</script>

<template>
  <div class="container mt-3">
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <div v-else>
              <div class="d-flex justify-content-end mb-3">
                <button
                  class="btn btn-secondary"
                  @click="router.push({ path: '/category/index' })"
                >
                  Kembali
                </button>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Kategori</label>
                <p class="form-control-plaintext">{{ kategori }}</p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Deskripsi</label>
                <p class="form-control-plaintext" style="white-space: pre-line">
                  {{ deskripsi || "-" }}
                </p>
              </div>
            </div>
          </div>
        </div>
        <div>
          <h2 class="text-2xl font-semibold mb-2 mt-4 text-white">
            Riwayat Perubahan
          </h2>
        </div>
        <div class="card border-0 rounded shadow mb-4">
          <div class="card-body">
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>
            <table v-else class="table table-striped table-bordered">
              <thead>
                <tr>
                  <th>Tanggal</th>
                  <th>Event</th>
                  <th>User</th>
                  <th>Perubahan</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="audit in audits" :key="audit.id">
                  <td>{{ formatTanggal(audit.created_at) }}</td>
                  <td>{{ mapEvent(audit.event) }}</td>
                  <td>{{ getUserName(audit) }}</td>
                  <td>
                    <pre style="white-space: pre-wrap; margin: 0"
                      >{{ formatChanges(audit.old_values, audit.new_values) }}
                      </pre
                    >
                  </td>
                </tr>
                <tr v-if="audits.length === 0">
                  <td colspan="4" class="text-center">
                    Belum ada riwayat perubahan
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
