<script setup lang="ts">
import Sidebar from '@/components/Sidebar.vue';
import axios from 'axios';
import { Edit, Plus, Search, Trash2, X, User, Phone } from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Interface Data
interface Pelanggan {
    id_pelanggan: number;
    nama_pelanggan: string;
    kontak: string;
    created_at?: string;
}

// State
const pelangganList = ref<Pelanggan[]>([]);
const searchQuery = ref('');
const showModal = ref(false);
const modalMode = ref<'add' | 'edit'>('add');
const isLoading = ref(false);

// Form State
const formData = ref({
    id: 0,
    nama_pelanggan: '',
    kontak: ''
});

// 1. Fetch Data
const fetchPelanggan = async () => {
    isLoading.value = true;
    try {
        const res = await axios.get('/api/pelanggan');
        pelangganList.value = res.data.data;
    } catch (e) {
        console.error(e);
        alert('Gagal mengambil data pelanggan');
    } finally {
        isLoading.value = false;
    }
};

// 2. Filter Pencarian
const filteredList = computed(() => {
    if (!searchQuery.value) return pelangganList.value;
    const lowerQuery = searchQuery.value.toLowerCase();
    return pelangganList.value.filter(p =>
        p.nama_pelanggan.toLowerCase().includes(lowerQuery) ||
        (p.kontak && p.kontak.includes(lowerQuery))
    );
});

// 3. Modal Actions
const openModal = (mode: 'add' | 'edit', data?: Pelanggan) => {
    modalMode.value = mode;
    if (mode === 'edit' && data) {
        formData.value = {
            id: data.id_pelanggan,
            nama_pelanggan: data.nama_pelanggan,
            kontak: data.kontak
        };
    } else {
        formData.value = { id: 0, nama_pelanggan: '', kontak: '' };
    }
    showModal.value = true;
};

// 4. Simpan Data (Add/Edit)
const saveData = async () => {
    if (!formData.value.nama_pelanggan) return alert('Nama wajib diisi');

    try {
        if (modalMode.value === 'add') {
            await axios.post('/api/pelanggan', formData.value);
            alert('Pelanggan berhasil ditambahkan');
        } else {
            await axios.put(`/api/pelanggan/${formData.value.id}`, formData.value);
            alert('Pelanggan berhasil diperbarui');
        }
        showModal.value = false;
        fetchPelanggan();
    } catch (e: any) {
        console.error(e);
        alert(e.response?.data?.message || 'Gagal menyimpan data');
    }
};

// 5. Hapus Data
const deleteData = async (id: number) => {
    if(confirm('Yakin ingin menghapus pelanggan ini?')) {
        try {
            await axios.delete(`/api/pelanggan/${id}`);
            alert('Pelanggan berhasil dihapus');
            fetchPelanggan();
        } catch (e) {
            alert('Gagal menghapus data');
        }
    }
}

// Helper: Input Hanya Angka untuk Kontak
const ruleInputAngka = (event: any) => {
    let val = event.target.value.replace(/\D/g, '');
    event.target.value = val;
    formData.value.kontak = val;
}

onMounted(fetchPelanggan);
</script>

<template>
    <Sidebar>
        <div class="space-y-6 font-['Inter']">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900">Manajemen Pelanggan</h2>
                    <p class="text-sm text-gray-500">Database pelanggan untuk dropdown otomatis.</p>
                </div>
                <button @click="openModal('add')" class="bg-blue-600 text-white px-4 py-2.5 rounded-lg flex items-center gap-2 hover:bg-blue-700 transition-colors shadow-sm">
                    <Plus class="w-4 h-4"/>
                    <span class="font-medium">Tambah Pelanggan</span>
                </button>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="p-4 border-b border-gray-100 bg-gray-50/50">
                    <div class="relative max-w-sm">
                        <Search class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" />
                        <input v-model="searchQuery" type="text" placeholder="Cari nama atau kontak..."
                            class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all text-sm">
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm text-left">
                        <thead class="bg-gray-50 uppercase text-xs text-gray-500 font-semibold tracking-wider">
                            <tr>
                                <th class="px-6 py-3 w-16 text-center">No</th>
                                <th class="px-6 py-3">Nama Pelanggan</th>
                                <th class="px-6 py-3">Kontak (HP/WA)</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="isLoading">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Memuat data...</td>
                            </tr>
                            <tr v-else-if="filteredList.length === 0">
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">Data tidak ditemukan.</td>
                            </tr>
                            <tr v-for="(p, index) in filteredList" :key="p.id_pelanggan" class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 text-center text-gray-500">{{ index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-blue-100 flex items-center justify-center text-blue-600">
                                            <User class="w-4 h-4" />
                                        </div>
                                        <span class="font-medium text-gray-900">{{ p.nama_pelanggan }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-gray-600">
                                    <div class="flex items-center gap-2">
                                        <Phone class="w-3.5 h-3.5 text-gray-400" />
                                        {{ p.kontak || '-' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex justify-center gap-2">
                                        <button @click="openModal('edit', p)" class="p-2 rounded-lg text-blue-600 hover:bg-blue-50 transition-colors" title="Edit">
                                            <Edit class="w-4 h-4"/>
                                        </button>
                                        <button @click="deleteData(p.id_pelanggan)" class="p-2 rounded-lg text-red-600 hover:bg-red-50 transition-colors" title="Hapus">
                                            <Trash2 class="w-4 h-4"/>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 bg-black/50 backdrop-blur-sm flex items-center justify-center z-[60] p-4 transition-opacity">
            <div class="bg-white rounded-xl shadow-xl w-full max-w-md overflow-hidden transform transition-all">
                <div class="flex justify-between items-center p-5 border-b border-gray-100">
                    <h3 class="font-bold text-lg text-gray-900">{{ modalMode === 'add' ? 'Tambah' : 'Edit' }} Pelanggan</h3>
                    <button @click="showModal = false" class="text-gray-400 hover:text-gray-600 bg-gray-100 hover:bg-gray-200 p-1 rounded-full transition-colors">
                        <X class="w-5 h-5"/>
                    </button>
                </div>

                <div class="p-6 space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Nama Pelanggan <span class="text-red-500">*</span></label>
                        <input v-model="formData.nama_pelanggan" type="text" placeholder="Contoh: PT Telkom Akses"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1.5">Kontak</label>
                        <input :value="formData.kontak" @input="ruleInputAngka" type="text" placeholder="08123456789"
                            class="w-full border border-gray-300 rounded-lg px-3 py-2.5 focus:ring-2 focus:ring-blue-500 focus:border-blue-500 outline-none transition-all">
                        <p class="text-xs text-gray-500 mt-1">Hanya angka diperbolehkan.</p>
                    </div>

                    <div class="pt-2 flex gap-3">
                        <button @click="showModal = false" class="flex-1 px-4 py-2.5 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 font-medium transition-colors">Batal</button>
                        <button @click="saveData" class="flex-1 px-4 py-2.5 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors">Simpan</button>
                    </div>
                </div>
            </div>
        </div>
    </Sidebar>
</template>
