<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();
const route = useRoute();

const title = ref("");
const category_id = ref("");
const categories = ref([]); // list kategori untuk lookup
const categoryName = ref(""); // nama kategori hasil lookup
const published_at = ref("");
const is_available = ref("");
const details = ref("");
const bookFilePath = ref("");
const isLoading = ref(true);
const audits = ref([]);
const isAuditLoading = ref(true);
const users = ref([]);

onMounted(async () => {
  try {
    // Ambil data kategori dulu
    const catResponse = await api.get("/api/categories");
    categories.value = catResponse.data.data || catResponse.data;

    // Ambil list users
    const userResponse = await api.get("/api/users");
    users.value = userResponse.data.data || userResponse.data;

    // Ambil data buku
    const bookResponse = await api.get(`/api/books/${route.params.id}`);
    const book = bookResponse.data.data || bookResponse.data;

    title.value = book.title || "";
    category_id.value = book.category_id || "";
    published_at.value = book.published_at || "";
    is_available.value = book.is_available || "";
    details.value = book.details || "";
    bookFilePath.value = book.file_path || "";

    // Cari nama kategori sesuai category_id
    const foundCategory = categories.value.find(
      (cat) => cat.id === category_id.value
    );
    categoryName.value = foundCategory
      ? foundCategory.name || foundCategory.kategori
      : "";
    await fetchAudits();
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Gagal mengambil data",
      text: "Buku atau kategori tidak ditemukan atau terjadi kesalahan."
    }).then(() => {
      router.push({ path: "/book/index" });
    });
  } finally {
    isLoading.value = false;
  }
});
async function fetchAudits() {
  try {
    isAuditLoading.value = true;
    const res = await api.get(`/api/books/${route.params.id}/audits`);
    audits.value = res.data.data || res.data;
  } catch (error) {
    console.error("Gagal ambil data audit:", error);
  } finally {
    isAuditLoading.value = false;
  }
}

function getUserName(audit) {
  // Cari user dari tabel users berdasarkan user_id di audit
  const foundUser = users.value.find((u) => u.id === audit.user_id);
  return foundUser ? foundUser.name : "-";
}

function formatTanggal(dateStr) {
  const bulan = [
    "Januari",
    "Februari",
    "Maret",
    "April",
    "Mei",
    "Juni",
    "Juli",
    "Agustus",
    "September",
    "Oktober",
    "November",
    "Desember"
  ];
  const dt = new Date(dateStr);
  const tanggal = dt.getDate();
  const bulanStr = bulan[dt.getMonth()];
  const tahun = dt.getFullYear();
  return `${tanggal} ${bulanStr} ${tahun}`;
}

function mapEvent(event) {
  const map = {
    created: "Menambah",
    updated: "Mengubah",
    deleted: "Menghapus"
  };
  return map[event] || event;
}
const fieldMap = {
  title: "Judul",
  category_id: "Kategori",
  published_at: "Tahun publikasi",
  is_available: "Jumlah ketersediaan",
  details: "Detail",
  file_path: "File PDF"
};

function formatChanges(oldValues, newValues) {
  try {
    const oldObj =
      typeof oldValues === "string" ? JSON.parse(oldValues) : oldValues || {};
    const newObj =
      typeof newValues === "string" ? JSON.parse(newValues) : newValues || {};
    const changes = [];
    for (const key in { ...oldObj, ...newObj }) {
      if ((key === "id") | (key === "category_name_snapshot")) continue;
      if (oldObj[key] !== newObj[key]) {
        const fieldName = fieldMap[key] || key;

        let oldVal = oldObj[key] || "-";
        let newVal = newObj[key] || "-";

        // 🔹 kalau kategori, ganti ID jadi nama
        if (key === "category_id") {
          const oldCat = categories.value.find((c) => c.id == oldVal);
          const newCat = categories.value.find((c) => c.id == newVal);
          oldVal = oldCat ? oldCat.name : "-";
          newVal = newCat ? newCat.name : "-";
        }

        changes.push(`${fieldName}: "${oldVal}" → "${newVal}"`);
      }
    }

    return changes.length ? changes.join("\n") : "-";
  } catch {
    return "-";
  }
}
function getCategoryNameById(categoryId) {
  const category = categories.value.find((b) => b.id === categoryId);
  return category ? category.name : "-";
}
function formatMasterCategoryChange(audit) {
  const oldVals = parseJsonIfString(audit.old_values);
  const newVals = parseJsonIfString(audit.new_values);

  const oldVal = oldVals.category_title_snapshot || "-";
  const newVal = getCategoryNameById(newVals.category_id) || "-";

  console.log("oldVal:", oldVal, "newVal:", newVal);

  if (oldVal === "-" && newVal === "-") {
    return "";
  } else if (oldVal === "-" && newVal !== "-") {
    return "";
  } else if (oldVal !== "-" && newVal === "-") {
    return `[Master] Kategori: "${oldVal}" → "${newVal}"`;
  } else {
    return `[Master] Kategori: "${oldVal}" → "${newVal}"`;
  }
}

function parseJsonIfString(value) {
  if (!value) return {};
  if (typeof value === "string") {
    try {
      return JSON.parse(value);
    } catch {
      return {};
    }
  }
  return value;
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
              <div class="d-flex justify-content-end">
                <router-link
                  :to="{ name: 'book.edit', params: { id: route.params.id } }"
                  class="btn btn-primary"
                  title="Edit"
                >
                  <i class="bi bi-pencil"></i> Edit
                </router-link>
                <button
                  class="btn btn-secondary ml-2"
                  @click="router.push({ path: '/book/index' })"
                >
                  Kembali
                </button>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Judul</label>
                <p class="form-control-plaintext">{{ title }}</p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Kategori</label>
                <p class="form-control-plaintext">{{ categoryName }}</p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Tahun Publikasi</label>
                <p class="form-control-plaintext">{{ published_at }}</p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Jumlah Ketersediaan</label>
                <p class="form-control-plaintext">{{ is_available }}</p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Detail</label>
                <p
                  class="form-control-plaintext"
                  style="
                    white-space: pre-line;
                    min-height: 100px;
                    border: 1px solid #ddd;
                    padding: 10px;
                    border-radius: 4px;
                  "
                >
                  {{ details || "-" }}
                </p>
              </div>

              <div class="mb-3" v-if="bookFilePath">
                <label class="form-label fw-bold d-block mb-2">File PDF</label>
                <a
                  :href="`http://localhost:8000/storage/${bookFilePath}`"
                  target="_blank"
                  rel="noopener noreferrer"
                  class="btn btn-outline-primary"
                >
                  Lihat Buku
                </a>
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
          <div>
            <div v-if="isAuditLoading" class="text-center my-3">
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
                    <pre style="white-space: pre-wrap"
                      >{{ formatChanges(audit.old_values, audit.new_values) }}
                      </pre
                    >

                    <pre
                      v-if="formatMasterCategoryChange(audit)"
                      style="white-space: pre-wrap"
                      >{{ formatMasterCategoryChange(audit) }}
</pre
                    >
                  </td>
                </tr>
                <tr v-if="audits.length === 0">
                  <td colspan="4" class="text-center">
                    Tidak ada riwayat perubahan
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
