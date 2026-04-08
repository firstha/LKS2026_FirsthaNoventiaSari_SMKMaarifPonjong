<template>
  <div style="max-width: 400px; margin: 100px auto; padding: 20px; border: 1px solid #ccc;">
    <h2>Login UMKM</h2>
    
    <div>
      <input type="email" v-model="email" placeholder="Email" style="width: 100%; padding: 8px; margin: 10px 0;" />
    </div>
    
    <div>
      <input type="password" v-model="password" placeholder="Password" style="width: 100%; padding: 8px; margin: 10px 0;" />
    </div>
    
    <div>
      <select v-model="role" style="width: 100%; padding: 8px; margin: 10px 0;">
        <option value="applicant">Applicant</option>
        <option value="verifier">Verifier</option>
        <option value="analyst">Analyst</option>
        <option value="manager">Manager</option>
      </select>
    </div>
    
    <button @click="login" style="width: 100%; padding: 10px; background: blue; color: white;">Login</button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      password: '',
      role: 'applicant'
    }
  },
  methods: {
    login() {
      localStorage.setItem('token', '123')
      localStorage.setItem('role', this.role)
      this.$router.push('/dashboard')
    }
  }
}
</script>