<template>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow-sm">
                    <div class="card-header">
                        <h4 class="mb-0">Prijava na sistem</h4>
                    </div>
                    <div class="card-body">
                        <form @submit.prevent="submitLogin">
                            <div class="mb-3">
                                <label class="form-label">Email</label>
                                <input type="email" class="form-control" v-model="form.email" required />
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Lozinka</label>
                                <input type="password" class="form-control" v-model="form.password" required />
                                <a class="btn btn-link p-0" data-bs-toggle="modal" data-bs-target="#resetPasswordModal">
                                    Zaboravili ste lozinku?
                                </a>
                            </div>

                            <button type="submit" class="btn btn-warning w-100" :disabled="loading">
                                {{ loading ? 'Sačekajte...' : 'Prijavi se' }}
                            </button>
                        </form>

                        <p v-if="activationResendBtn" class="error">
                            Vaš nalog nije aktiviran. Molimo Vas da aktivirate svoj nalog putem linka koji ste dobili na email adresu.
                            <a @click="resendActivation" class="btn btn-link py-0">Pošalji ponovo link za aktivaciju</a>
                        </p>

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

        <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="resetPasswordModalLabel">Promena lozinke</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p>Unesite email adresu sa kojom ste se registrovali. <br>
                        Na email adresu će Vam stići link za promenu lozinke.</p>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" class="form-control" v-model="resetEmail" required />
                        </div>

                        <div v-if="successResetMessage" class="alert alert-success mt-3">
                            {{ successResetMessage }}
                        </div>

                        <div v-if="resetEmailErrorMessage" class="alert alert-danger mt-3">
                            {{ resetEmailErrorMessage }}
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="resetPassword" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Resetuj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>

<script>
import axios from 'axios';

export default {
    name: 'LoginForm',
    data() {
        return {
            form: {
                email: '',
                password: '',
            },

            loading: false,
            errorMessage: '',
            successMessage: '',
            activationResendBtn: false,

            resetEmail: '',
            resetEmailErrorMessage: '',
            successResetMessage: '',

        };
    },
    methods: {
        async submitLogin() {
            this.successMessage = '';
            this.errorMessage = '';
            this.loading = true;

            try {
                await axios.post('/submitLogin', this.form);
                window.location.href = '/taskAdministration';

            } catch (error) {
                // console.error(error);
                if(error.status === 403) {
                    this.errorMessage = '';
                    this.activationResendBtn = true;
                } else if (error.response && error.response.data.message) {
                    this.errorMessage = error.response.data.message;
                    this.activationResendBtn = false;
                } else {
                    this.errorMessage = 'Došlo je do greške prilikom prijave. Molimo pokušajte ponovo.';
                    this.activationResendBtn = false;
                }
                
            } finally {
                this.loading = false;
            }
        },
        async resendActivation() {
            this.successMessage = '';
            this.errorMessage = '';

            try {
                const response = await axios.post('/resendActivationLink', { email: this.form.email });
                this.successMessage = response.data.message;
                this.activationResendBtn = false;

            } catch (error) {
                this.successMessage = '';
                this.activationResendBtn = true;

                if (error.response && error.response.data.message) {
                    this.errorMessage = error.response.data.message;
                }
                else {
                    this.errorMessage = 'Greška prilikom slanja aktivacionog linka.';
                }
            }
        },
        resetPassword(){
            this.successResetMessage = '';
            this.resetEmailErrorMessage = '';
            this.loading = true;

            if(this.resetEmail === '') {
                this.resetEmailErrorMessage = 'Molimo unesite email adresu.';
                this.loading = false;
                return;
            }

            axios.post('/sendResetLink', { email: this.resetEmail})
                .then(response => {
                    this.successResetMessage = response.data.message;
                    this.resetEmailErrorMessage = '';
                })
                .catch(error => {
                    if (error.response && error.response.data.message) {
                        this.resetEmailErrorMessage = error.response.data.message;
                    } else {
                        this.resetEmailErrorMessage = 'Greška prilikom slanja linka za promenu lozinke.';
                    }
                })
                .finally(() => {
                    this.loading = false;
                });
            
        }

    }
};
</script>
