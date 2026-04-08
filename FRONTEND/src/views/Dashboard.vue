<template>
  <div style="padding: 20px;">
    <div style="background: blue; color: white; padding: 10px; display: flex; justify-content: space-between;">
      <h3>Dashboard UMKM</h3>
      <button @click="logout" style="background: red; color: white;">Logout</button>
    </div>
    
    <div style="margin: 20px 0;">
      <p>Role: {{ role }}</p>
    </div>
    
    <div style="display: flex; gap: 10px; margin-bottom: 20px;">
      <button @click="page = 'business'" style="padding: 8px 16px;">Verifikasi Usaha</button>
      <button @click="page = 'financing'" style="padding: 8px 16px;">Ajukan Pembiayaan</button>
      <button @click="page = 'installments'" style="padding: 8px 16px;">Cicilan</button>
    </div>
    
    <div style="border: 1px solid #ccc; padding: 20px;">
      <!-- Verifikasi Usaha -->
      <div v-if="page === 'business'">
        <h3>Verifikasi Usaha</h3>
        <input v-model="business.nama" placeholder="Nama Usaha" style="width: 100%; padding: 8px; margin: 5px 0;" />
        <input v-model="business.nib" placeholder="NIB" style="width: 100%; padding: 8px; margin: 5px 0;" />
        <input v-model="business.npwp" placeholder="NPWP" style="width: 100%; padding: 8px; margin: 5px 0;" />
        <input v-model="business.omzet" placeholder="Omzet" style="width: 100%; padding: 8px; margin: 5px 0;" />
        <button @click="submitBusiness" style="margin-top: 10px; padding: 8px 16px; background: green; color: white;">Submit</button>
      </div>
      
      <!-- Pengajuan Pembiayaan -->
      <div v-if="page === 'financing'">
        <h3>Pengajuan Pembiayaan</h3>
        <input v-model="financing.jumlah" placeholder="Jumlah" style="width: 100%; padding: 8px; margin: 5px 0;" />
        <select v-model="financing.tenor" style="width: 100%; padding: 8px; margin: 5px 0;">
          <option value="6">6 Bulan</option>
          <option value="12">12 Bulan</option>
          <option value="24">24 Bulan</option>
        </select>
        <textarea v-model="financing.tujuan" placeholder="Tujuan" style="width: 100%; padding: 8px; margin: 5px 0;"></textarea>
        <button @click="submitFinancing" style="margin-top: 10px; padding: 8px 16px; background: orange;">Ajukan</button>
      </div>
      
      <!-- Cicilan -->
      <div v-if="page === 'installments'">
        <h3>Jadwal Cicilan</h3>
        <table style="width: 100%; border-collapse: collapse;">
          <tr style="background: #eee;">
            <th style="border: 1px solid #ccc; padding: 8px;">#</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Tanggal</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Jumlah</th>
            <th style="border: 1px solid #ccc; padding: 8px;">Status</th>
          </tr>
          <tr v-for="item in installments" :key="item.id">
            <td style="border: 1px solid #ccc; padding: 8px;">{{ item.id }}</td>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ item.date }}</td>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ item.amount }}</td>
            <td style="border: 1px solid #ccc; padding: 8px;">{{ item.status }}</td>
          </tr>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      role: localStorage.getItem('role') || 'applicant',
      page: 'business',
      business: {
        nama: '',
        nib: '',
        npwp: '',
        omzet: ''
      },
      financing: {
        jumlah: '',
        tenor: 12,
        tujuan: ''
      },
      installments: [
        { id: 1, date: '2025-02-01', amount: 'Rp 2.208.333', status: 'Lunas' },
        { id: 2, date: '2025-03-01', amount: 'Rp 2.208.333', status: 'Lunas' },
        { id: 3, date: '2025-04-01', amount: 'Rp 2.208.333', status: 'Belum' }
      ]
    }
  },
  methods: {
    submitBusiness() {
      alert('Verifikasi diajukan: ' + this.business.nama)
    },
    submitFinancing() {
      alert('Pengajuan diajukan: Rp ' + this.financing.jumlah)
    },
    logout() {
      localStorage.clear()
      this.$router.push('/')
    }
  }
}
</script>