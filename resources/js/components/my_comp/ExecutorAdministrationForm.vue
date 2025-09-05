<template>
    <div class="container mt-4">
        <nav class="mt-5">
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-tasks-tab" data-bs-toggle="tab" data-bs-target="#nav-task" type="button" role="tab" aria-controls="nav-task" aria-selected="false">Zadaci</button>
            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
            <!-- Zadaci -->
            <div class="tab-pane fade show active" id="nav-task" role="tabpanel" aria-labelledby="nav-task-tab" tabindex="0">

                <!-- Filteri -->
                <div class="card p-3 mb-4 shadow-sm">
                    <h5 class="mb-3">Filteri zadataka</h5>
                    
                    <div class="row g-3">
                        <!-- Izvršioci -->
                        <div class="col-md-2">
                            <label for="executor" class="form-label">Izvršilac</label>
                            <select v-model="filters.executor_id" class="form-select" id="executor">
                                <option value="">Svi</option>
                                <option v-for="user in users_filters" :key="user.id" :value="user.id">{{ user.full_name }}</option>
                            </select>
                        </div>

                        <!-- Rukovodioci -->
                        <div class="col-md-2">
                            <label for="executor" class="form-label">Rukovodilac</label>
                            <select v-model="filters.manager_id" class="form-select" id="executor">
                                <option value="">Svi</option>
                                <option v-for="user in users_filters" :key="user.id" :value="user.id">{{ user.full_name }}</option>
                            </select>
                        </div>

                        <!-- Datun izvršenja -->
                        <div class="col-md-3">
                            <label for="dateFrom" class="form-label">Datum izvršenja</label>
                            <input type="date" v-model="filters.deadline" class="form-control" id="dateFrom">
                        </div>

                    </div>

                    <!-- Dugmad -->
                    <div class="mt-3">
                        <button @click="applyFilters" class="btn btn-primary me-2">Primeni filtere</button>
                        <button @click="resetFilters" class="btn btn-secondary">Resetuj</button>
                    </div>
                </div>

                <table id="tasks-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th></th>
                            <th class="text-start">Naziv</th>
                            <th class="text-start">Opis</th>
                            <th class="text-start">Rok izvršenja</th>
                            <th class="text-start">Prioritet</th>
                            <th class="text-end">Akcije</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

         <!-- Modal za status taska-->
        <div class="modal fade" id="statusTaskModal" tabindex="-1" aria-labelledby="statusTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="statusTaskModalLabel">Status zadatka</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input v-model="taskStatus.status" class="form-check-input" type="radio" name="task_status" value="0" id="pendingBtn">
                                <label class="form-check-label" for="pendingBtn">
                                    U radu
                                </label>
                            </div>
                            <div class="form-check">
                                <input v-model="taskStatus.status" class="form-check-input" type="radio" name="task_status" value="1" id="completedBtn">
                                <label class="form-check-label" for="completedBtn">
                                    Završeno
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="saveStatus" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal za KOMENTARE taska-->
        <div class="modal fade" id="commentTaskModal" tabindex="-1" aria-labelledby="commentTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="commentTaskModalLabel">Komentari zadatka</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3 pb-5">
                            <label class="form-label">Dodaj komentar</label>
                            <textarea class="form-control" v-model="newComment" rows="2" placeholder="Unesite komentar"></textarea>
                            <button class="btn btn-primary mt-2 float-end" @click="addComment" :disabled="loading">
                                Dodaj komentar
                            </button>
                        </div>
                        <div v-if="taskComments.length" class="mt-3 pt-3">
                            <ul class="list-group">
                                <li v-for="comment in taskComments" :key="comment.id" class="list-group-item">
                                    <strong>Korisnik: </strong>{{ comment.user_full_name }} <br> 
                                    <strong>Komentar: </strong>{{ comment.comment }}
                                    <button @click="removeComments(comment.id)" class="btn btn-sm btn-outline-danger float-end">
                                        <i class="fas fa-trash-alt"></i> Obriši
                                    </button>

                                </li>
                            </ul>
                        </div>
                        <p v-else class="mt-3 pt-3">Nema komentara za ovaj zadatak.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="commentTask" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
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
    name: 'ExecutorAdministrationForm',
    data() {
        return {
            loading: false,
           filters: {
                deadline: '',
                manager_id: '',
                executor_id: ''
            },
            users_filters: [],

            taskStatus: {
                id: null,
                status: ''
            },

            taskComments: [],
            newComment: '',
            taskCommentsID: null,

        };
    },
    methods: {
       resetFilters() {
            this.filters = {
                deadline: '',
                manager_id: '',
                executor_id: ''
            };
            this.tasksDataTable();
        },
        applyFilters(){
            this.tasksDataTable();
        },
        tasksDataTable(){
            $('#tasks-table').DataTable().clear().destroy();
            $('#tasks-table').DataTable({
                language: {
                    paginate: {
                        next: ">",
                        previous: "<"
                    },
                    emptyTable: "Nema podataka u tabeli",
                    lengthMenu: "Prikaži _MENU_ unosa",
                    zeroRecords: "Nijedan unos nije pronađen",
                    info: "Prikazano _START_ do _END_ od ukupno _TOTAL_ unosa",
                    infoEmpty: "Nema dostupnih unosa",
                    infoFiltered: "(filtrirano iz ukupno _MAX_ unosa)",
                    search: "Pretraga:",
                    processing: "Sačekajte..."
                },
                processing: true,
                serverSide: true,
                ajax: {
                    url: '/returnAllTasksExec',
                    type: "GET",
                    data: {
                        filters: this.filters
                    },
                },
                columnDefs: [
                    { targets: 0, orderable: true },
                    { targets: 1, orderable: true },
                    { targets: 2, orderable: false },
                    { targets: 3, orderable: false },
                    { targets: 4, orderable: false },

                ],
                columns: [
                    {
                        className: 'dt-control',
                        orderable: false,
                        data: null,
                        defaultContent: '',
                        width: "20px"
                    },
                    {
                        data: "task_title",
                        className: "text-start",
                        orderable: true,
                    },
                    {
                        data : 'task_description',
                        className: "text-start",
                        orderable: false,
                    },
                    {
                        data: "task_deadline",
                        className: "text-start",
                        orderable: false,
                        render: function (data, type, row) {
                            return data ? new Date(data).toLocaleDateString('sr-RS') : 'Nema roka';
                        }
                    },
                    {
                        data: "task_priority",
                        className: "text-start",
                        orderable: false,
                    },
                    {
                        data: "akcije",
                        orderable: false,
                        render: function (data, type, row) {
                            //ikonice ne rade 
                            return '<span class="dropdown float-end">'+
                                    '<a href="#" class="btn dropdown-toggle" data-bs-toggle="dropdown">'+
                                        '   <i class="fas fa-ellipsis-v"></i>'+
                                    ' </a>'+
                                    '<span class="dropdown-menu">' +
                                        '<a class="dropdown-item statusTaskAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#statusTaskModal">'+
                                            '<i class="fas fa-gears me-2"></i>' + "Status zadatka" +
                                        '</a>'+
                                        '<a class="dropdown-item commentTaskAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#commentTaskModal">'+
                                            '<i class="fas fa-comment me-2"></i>' + "Dodaj komentar" +
                                        '</a>'+
                                    '</span>'+
                                '</span>'
                        }
                    }
                ]
            });

            // Dodavanje funkcionalnosti za proširivanje reda
            $('#tasks-table tbody').off('click').on('click', 'td.dt-control', function () {
                var tr = $(this).closest('tr');
                var row = $('#tasks-table').DataTable().row(tr);

                if (row.child.isShown()) {
                    row.child.hide();
                    tr.removeClass('shown');
                } else {

                    const managerHtml = row.data().manager_name || 'Nema kreatora';
                    const groupHtml = row.data().group_name || 'Nema grupe';

                    const executorHtml = row.data().executor_names
                        .split(', ')
                        .map(name => `<li>${name}</li>`)
                        .join('');

                    row.child(`<div class="p-2">
                        <p><strong>Kreator zadatka:</strong> ${managerHtml}</p>
                        <p><strong>Grupa:</strong> ${groupHtml}</p>
                        <p><strong>Izvršioci:</strong></p><ul>${executorHtml}</ul></div>`).show();
                    tr.addClass('shown');
                }
            });

        },
        statusTaskAction(){
            $(document).on("click", ".statusTaskAction", (e) => {
                const taskId = $(e.currentTarget).data('entry-id');
                axios.get(`/getCompletedStatusTask/${taskId}`)
                    .then(response => {
                        // console.log(response.data)
                        this.taskStatus.status = response.data.status;
                        this.taskStatus.id = response.data.id;
                    })
                    .catch(error => {
                        console.error('Greška prilikom dobijanja podataka o statusu zadatka:', error);
                    });
            });
        },
        saveStatus(){
            // console.log(this.taskStatus)
            this.loading = true;

            axios.post('/saveCompletedStatus', { taskStatus: this.taskStatus })
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.taskStatus = { id: null, status: ''};
                    this.tasksDataTable(); 
                    $('#statusTaskModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom promene statusa za zadatak:', error);
                });
        },
        getUsers() {
            axios.get('/getUsers')
                .then(response => {
                    this.users_filters = response.data.map(user => ({
                        id: user.id,
                        full_name: user.full_name
                    }));
                })
                .catch(error => {
                    console.error('Greška prilikom dobijanja korisnika:', error);
                });
        },
        commentTaskAction(){
            $(document).on("click", ".commentTaskAction", (e) => {
                this.taskCommentsID = $(e.currentTarget).data('entry-id');
                axios.get(`/getCommentTaskForExecutor/${this.taskCommentsID}`)
                    .then(response => {
                        this.taskComments = response.data;
                    })
                    .catch(error => {
                        console.error('Greška prilikom dobijanja podataka o statusu zadatka:', error);
                    });
            });
        },
        removeComments(commentId) {
            this.taskComments = this.taskComments.filter(comment => comment.id !== commentId);
        },
        addComment(){
            if (!this.newComment.trim()) {
                alert('Komentar ne može biti prazan!');
                return;
            }
            
            this.taskComments.push({
                id: null,
                user_full_name: window.currentUser.full_name,
                comment: this.newComment
            })

            this.newComment = '';
        },
        commentTask(){
            // console.log(this.taskComments)
            this.loading = true;

            axios.post('/saveCommentsForExecutor', { taskComments: this.taskComments, task_id:this.taskCommentsID })
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.taskComments = [];
                    this.taskCommentsID = null;
                    this.newComment = '';
                    this.tasksDataTable(); 
                    $('#commentTaskModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom promene statusa za zadatak:', error);
                });

        },
    },
    mounted() {
        this.tasksDataTable();
        this.statusTaskAction();
        this.getUsers();
        this.commentTaskAction();

    }
};
</script>
<style>
.required::after {
    content: ' *';
    color: red;
}
</style>
