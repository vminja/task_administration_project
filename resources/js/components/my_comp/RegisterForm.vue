<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Registracija</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitForm">
                            <div class="mb-3">
                                <label class="form-label">Korisničko ime</label>
                                <input type="text" class="form-control" v-model="form.username" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ime i prezime</label>
                                <input type="text" class="form-control" v-model="form.full_name" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" v-model="form.email" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Telefon (opciono)</label>
                                <input type="text" class="form-control" v-model="form.phone" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Datum rođenja (opciono)</label>
                                <input type="date" class="form-control" v-model="form.birth_date" />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lozinka</label>
                                <input type="password" class="form-control" v-model="form.password" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Ponovi lozinku</label>
                                <input type="password" class="form-control" v-model="form.password_confirmation" required />
                            </div>

                            <button type="submit" class="btn btn-warning w-100" :disabled="loading">
                                {{ loading ? 'Sačekajte...' : 'Registruj se' }}
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
    name: 'RegisterForm',
    data() {
        return {
            form: {
                username: '',
                full_name: '',
                email: '',
                phone: '',
                birth_date: '',
                password: '',
                password_confirmation: ''
            },

            successMessage: '',
            errorMessage: '',
            loading: false
        };
    },
    methods: {
        async submitForm() {
            this.successMessage = '';
            this.errorMessage = '';
            this.loading = true;

            try {
                const response = await axios.post('/subminRegister', this.form);
                this.successMessage = response.data.message;
                this.form = {
                    username: '',
                    full_name: '',
                    email: '',
                    phone: '',
                    birth_date: '',
                    password: '',
                    password_confirmation: ''
                };

            } catch (error) {
                console.error(error);

                if (error.response.data.errors) {
                    this.errorMessage = Object.values(error.response.data.errors)[0][0];;
                } else {
                    this.errorMessage = 'Došlo je do greške prilikom registracije. Molimo pokušajte ponovo.';
                }
            } finally {
                this.loading = false;
            }
        }
    }
};
</script>