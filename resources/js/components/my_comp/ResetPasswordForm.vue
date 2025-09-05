<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Promena lozinke</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitNewPassword">
                            <div class="mb-3">
                                <label class="form-label">Nova lozinka</label>
                                <input type="password" class="form-control" v-model="newPassword.password" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ponovi lozinku</label>
                                <input type="password" class="form-control" v-model="newPassword.password_confirmation" required />
                            </div>

                            <button type="submit" class="btn btn-warning w-100" :disabled="loading">
                                {{ loading ? 'Sačekajte...' : 'Promeni lozinku' }}
                            </button>
                        </form>

                        <div v-if="successMessage" class="alert alert-success mt-3">
                            {{ successMessage }}
                        </div>

                        <div v-if="errorMessage" class="alert alert-danger mt-3">
                            {{ errorMessage }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

       
    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'ResetPasswordForm',
    props: {
        email: String,
        token: String
    },
    data() {
        return {
            newPassword: {
                password: '',
                password_confirmation: ''
            },

            loading: false,
            errorMessage: '',
            successMessage: '',

           
        };
    },
    methods: {
        async submitNewPassword() {
            this.successMessage = '';
            this.errorMessage = '';
            this.loading = true;

            if(this.newPassword.password !== this.newPassword.password_confirmation) {
                this.errorMessage = 'Lozinke se ne poklapaju.';
                this.loading = false;
                return;
            }

            try {
                const response = await axios.post('/submitNewPassword',  {newPassword: this.newPassword, token: this.token});
                this.successMessage = response.data.message;

                setTimeout(() => {
                    window.location.href = '/login';   
                }, 2000);

            } catch (error) {
                // console.error(error);
                if(error.status === 404) {
                    this.errorMessage = error.response.data.message;
                } else if (error.response && error.response.data.message) {
                    this.errorMessage = error.response.data.message;
                } else {
                    this.errorMessage = 'Došlo je do greške prilikom prijave. Molimo pokušajte ponovo.';
                }
                
            } finally {
                this.loading = false;
            }
        },

    }
};
</script>
