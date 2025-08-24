<script setup>
import { ref, onMounted } from "vue";
import { useRouter, useRoute } from "vue-router";
import api from "../../api";
import Swal from "sweetalert2";

const router = useRouter();
const route = useRoute();

const loan = ref(null);
const member = ref(null);
const book = ref(null);
const isLoading = ref(true);

const audits = ref([]);
const isAuditLoading = ref(true);
const users = ref([]);
const members = ref([]);
const books = ref([]);

onMounted(async () => {
  try {
    // Ambil data loan dulu
    const loanRes = await api.get(`/api/loans/${route.params.id}`);
    loan.value = loanRes.data.data || loanRes.data;

    // Ambil data member
    if (loan.value?.member_id) {
      const memberRes = await api.get(`/api/members/${loan.value.member_id}`);
      member.value = memberRes.data.data || memberRes.data;
    }

    // Ambil data book
    if (loan.value?.book_id) {
      const bookRes = await api.get(`/api/books/${loan.value.book_id}`);
      book.value = bookRes.data.data || bookRes.data;
    }

    // Data lookup untuk audit
    const userRes = await api.get("/api/users");
    users.value = userRes.data.data || userRes.data;

    const memberResAll = await api.get("/api/members");
    members.value = memberResAll.data.data || memberResAll.data;

    const bookResAll = await api.get("/api/books");
    books.value = bookResAll.data.data || bookResAll.data;

    await fetchAudits();
  } catch (error) {
    Swal.fire({
      icon: "error",
      title: "Gagal mengambil data",
      text: "Data peminjaman atau audit tidak ditemukan."
    }).then(() => {
      router.push({ path: "/loan/index" });
    });
  } finally {
    isLoading.value = false;
  }
});

async function fetchAudits() {
  try {
    isAuditLoading.value = true;
    const res = await api.get(`/api/loans/${route.params.id}/audits`);
    audits.value = res.data.data || res.data;
  } catch (error) {
    console.error("Gagal ambil audit loan:", error);
  } finally {
    isAuditLoading.value = false;
  }
}

function getUserName(audit) {
  const found = users.value.find((u) => u.id === audit.user_id);
  return found ? found.name : "-";
}

function formatTanggal(dateStr) {
  if (!dateStr) return "-";
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
  return `${dt.getDate()} ${bulan[dt.getMonth()]} ${dt.getFullYear()}`;
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
  member_name_snapshot: "[Master] Anggota",
  member_id: "Anggota",
  book_id: "Buku",
  borrowed_at: "Tanggal Pinjam",
  returned_at: "Tanggal Kembali"
};

function formatChanges(oldValues, newValues) {
  try {
    const oldObj =
      typeof oldValues === "string" ? JSON.parse(oldValues) : oldValues || {};
    const newObj =
      typeof newValues === "string" ? JSON.parse(newValues) : newValues || {};
    const changes = [];

    for (const key in { ...oldObj, ...newObj }) {
      if (key === "id") continue;
      if (oldObj[key] !== newObj[key]) {
        const fieldName = fieldMap[key] || key;

        let oldVal = oldObj[key] || "-";
        let newVal = newObj[key] || "-";
        if (
          key === "book_title_snapshot" ||
          key === "book_category_snapshot" ||
          key === "member_name_snapshot"
        )
          continue;

        if (key === "book_id") {
          const oldBook = books.value.find((m) => m.id == oldVal);
          const newBook = books.value.find((m) => m.id == newVal);
          oldVal = oldBook ? oldBook.title : "-";
          newVal = newBook ? newBook.title : "-";
        }

        if (key === "member_id") {
          const oldMember = members.value.find((m) => m.id == oldVal);
          const newMember = members.value.find((m) => m.id == newVal);
          oldVal = oldMember ? oldMember.name : "-";
          newVal = newMember ? newMember.name : "-";
        }

        if (["borrowed_at", "returned_at"].includes(key)) {
          oldVal = oldVal !== "-" ? formatTanggal(oldVal) : "-";
          newVal = newVal !== "-" ? formatTanggal(newVal) : "-";
        }

        changes.push(`${fieldName}: "${oldVal}" → "${newVal}"`);
      }
    }

    return changes.length ? changes.join("\n") : "-";
  } catch {
    return "-";
  }
}
function getBookTitleById(bookId) {
  const book = books.value.find((b) => b.id === bookId);
  return book ? book.title : "-";
}
function getMemberNameById(memberId) {
  const member = members.value.find((b) => b.id === memberId);
  return member ? member.name : "-";
}
function formatMasterBookChange(audit) {
  const oldVals = parseJsonIfString(audit.old_values);
  const newVals = parseJsonIfString(audit.new_values);

  const oldVal = oldVals.book_title_snapshot || "-";
  const newVal = getBookTitleById(newVals.book_id) || "-";

  // Kondisi agar jika nilai baru adalah "-", jangan tampilkan
  if (oldVal === "-" && newVal === "-") {
    // Keduanya kosong, tidak tampilkan apa-apa
    return "";
  } else if (oldVal === "-" && newVal !== "-") {
    // Dari kosong ke ada, tampilkan hanya newVal
    return "";
  } else if (oldVal !== "-" && newVal === "-") {
    // Dari ada ke kosong, tampilkan perubahan
    return `[Master] Buku: "${oldVal}" → "${newVal}"`;
  } else {
    // Perubahan normal dari satu nilai ke nilai lain
    return `[Master] Buku: "${oldVal}" → "${newVal}"`;
}
}

function formatMasterMemberChange(audit) {
  const oldVals = parseJsonIfString(audit.old_values);
  const newVals = parseJsonIfString(audit.new_values);

  const oldVal = oldVals.member_name_snapshot || "-";
  const newVal = getMemberNameById(newVals.member_id) || "-";

  // Sama logika seperti member
  if (oldVal === "-" && newVal === "-") {
    // Keduanya kosong, tidak tampilkan apa-apa
    return "";
  } else if (oldVal === "-" && newVal !== "-") {
    // Dari kosong ke ada, tampilkan hanya newVal
    return "";
  } else if (oldVal !== "-" && newVal === "-") {
    // Dari ada ke kosong, tampilkan perubahan
    return `[Master] Anggota: "${oldVal}" → "${newVal}"`;
  } else {
    // Perubahan normal dari satu nilai ke nilai lain
    return `[Master] Anggota: "${oldVal}" → "${newVal}"`;
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
    <!-- ⬅ Detail loan asli kamu -->
    <div class="row">
      <div class="col-md-12">
        <div class="card border-0 rounded shadow">
          <div class="card-body">
            <div v-if="isLoading" class="text-center my-4">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
              </div>
            </div>

            <div v-else-if="!loan">
              <div class="alert alert-danger">
                Data peminjaman tidak ditemukan.
              </div>
            </div>

            <div v-else>
              <div class="d-flex justify-content-end">
                <button
                  class="btn btn-secondary rounded-sm shadow border-0 ml-2"
                  @click="router.push({ path: '/loan/index' })"
                >
                  Kembali
                </button>
              </div>
              <div class="mb-3">
                <label class="form-label fw-bold">Nama Anggota</label>
                <p class="form-control-plaintext">
                  {{ loan.member_name || "-" }}
                </p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Judul Buku</label>
                <p class="form-control-plaintext">
                  {{ loan.book_title || "-" }}
                </p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Peminjaman</label>
                <p class="form-control-plaintext">
                  {{ formatTanggal(loan.borrowed_at) }}
                </p>
              </div>

              <div class="mb-3">
                <label class="form-label fw-bold">Tanggal Pengembalian</label>
                <p class="form-control-plaintext">
                  {{
                    loan.returned_at
                      ? formatTanggal(loan.returned_at)
                      : "Belum Dikembalikan"
                  }}
                </p>
              </div>

              <div class="mb-3 d-flex align-items-center gap-2">
                <div v-if="!loan.returned_at">
                  <button
                    @click.prevent="confirmReturn(loan)"
                    class="btn btn-warning rounded-sm shadow border-0"
                  >
                    <i class="bi bi-box-arrow-in-left"></i> Kembalikan Buku
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div>
          <h2 class="text-2xl font-semibold mb-2 mt-4 text-white">
            Riwayat Perubahan
          </h2>
        </div>
        <div>
          <div class="card border-0 rounded shadow mb-4">
            <div class="card-body">
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
                        v-if="formatMasterMemberChange(audit)"
                        style="white-space: pre-wrap"
                        >{{ formatMasterMemberChange(audit) }}
                      </pre>
                      <pre
                        v-if="formatMasterBookChange(audit)"
                        style="white-space: pre-wrap"
                        >{{ formatMasterBookChange(audit) }}
                      </pre>
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
        <!-- ⬆ Audit table tambahan -->
      </div>
    </div>
  </div>
</template>
