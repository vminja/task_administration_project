<template>
    <div class="container mt-4">
        <div class="d-flex justify-content-start gap-3">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#groupModal">
                Nova grupa
            </button>

            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#taskModal">
                Novi zadatak
            </button>
        </div>

        <nav class="mt-5">
            <div class="nav nav-tabs" id="nav-tab" role="tablist">
                <button class="nav-link active" id="nav-groups-tab" data-bs-toggle="tab" data-bs-target="#nav-group" type="button" role="tab" aria-controls="nav-group" aria-selected="true">Grupe</button>
                <button class="nav-link" id="nav-tasks-tab" data-bs-toggle="tab" data-bs-target="#nav-task" type="button" role="tab" aria-controls="nav-task" aria-selected="false">Zadaci</button>
                <button v-if="currentUser.type === 'admin'" class="nav-link" id="nav-users-tab" data-bs-toggle="tab" data-bs-target="#nav-user" type="button" role="tab" aria-controls="nav-user" aria-selected="false">Korisnici</button>
            </div>
        </nav>

        <div class="tab-content" id="nav-tabContent">
             <!-- Grupe -->
            <div class="tab-pane fade show active" id="nav-group" role="tabpanel" aria-labelledby="nav-group-tab" tabindex="0">
                <table id="groups-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th class="text-start">Naziv</th>
                            <th class="text-start">Opis</th>
                            <th class="text-start">Kreator grupe</th>
                            <th class="text-end">Akcije</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Zadaci -->
            <div class="tab-pane fade" id="nav-task" role="tabpanel" aria-labelledby="nav-task-tab" tabindex="0">

                <!-- Filteri -->
                <div class="card p-3 mb-4 shadow-sm">
                    <h5 class="mb-3">Filteri zadataka</h5>
                    
                    <div class="row g-3">
                        
                        <!-- Naslov -->
                        <div class="col-md-2">
                            <label for="title" class="form-label">Naslov</label>
                            <input type="text" v-model="filters.title" class="form-control" id="title" placeholder="Unesi naslov">
                        </div>

                        <!-- Prioritet -->
                        <div class="col-md-2">
                            <label for="priority" class="form-label">Prioritet</label>
                            <select v-model="filters.priority" class="form-select" id="priority">
                                <option value="">Svi</option>
                                <option v-for="n in 10" :value="n">{{ n }}</option>
                            </select>
                        </div>

                        <!-- Izvršioci -->
                        <div class="col-md-2">
                            <label for="executor" class="form-label">Izvršilac</label>
                            <select v-model="filters.executor_id" class="form-select" id="executor">
                                <option value="">Svi</option>
                                <option v-for="user in users_filters" :key="user.id" :value="user.id">{{ user.full_name }}</option>
                            </select>
                        </div>

                        <!-- Rok izvršenja (od - do) -->
                        <div class="col-md-3">
                            <label for="dateFrom" class="form-label">Rok izvršenja (od)</label>
                            <input type="date" v-model="filters.date_from" class="form-control" id="dateFrom">
                        </div>
                        <div class="col-md-3">
                            <label for="dateTo" class="form-label">Rok izvršenja (do)</label>
                            <input type="date" v-model="filters.date_to" class="form-control" id="dateTo">
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
                            <th class="text-start">Status</th>
                            <th class="text-end">Akcije</th>
                        </tr>
                    </thead>
                </table>
            </div>

            <!-- Korisnici -->
            <div v-if="currentUser.type === 'admin'" class="tab-pane fade" id="nav-user" role="tabpanel" aria-labelledby="nav-user-tab" tabindex="0">
                <h3 class="my-4">Administracija korisnika</h3>

                <table id="users-table" class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Ime</th>
                            <th>Email</th>
                            <th>Korisničko ime</th>
                            <th>Tip korisnika</th>
                            <th>Akcije</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>

        <!-- Modal za grupe-->
        <div class="modal fade" id="groupModal" tabindex="-1" aria-labelledby="groupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="groupModalLabel">Nova grupa</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Naziv grupe</label>
                            <input type="text" class="form-control" v-model="newGroup.name" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea class="form-control" v-model="newGroup.description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="saveGroup" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal za EDIT grupe-->
        <div class="modal fade" id="editGroupModal" tabindex="-1" aria-labelledby="editGroupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editGroupModalLabel">Izmena grupe</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Naziv grupe</label>
                            <input type="text" class="form-control" v-model="newGroupEdit.name" required />
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea class="form-control" v-model="newGroupEdit.description" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="editGroup" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal za DELETE grupe-->
        <div class="modal fade" id="deleteGroupModal" tabindex="-1" aria-labelledby="deleteGroupModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteGroupModalLabel">Brisanje grupe</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Da li ste sigurni da želite da obrišete grupu?</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="deleteGroup" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal za taskove-->
        <div class="modal fade" id="taskModal" tabindex="-1" aria-labelledby="taskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="taskModalLabel">Novi zadatak</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Naslov</label>
                            <input type="text" class="form-control" v-model="newTask.title" maxlength="191" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea class="form-control" v-model="newTask.description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Izvršioci</label>
                            <div class="border p-3 rounded">
                                <div class="form-check" v-for="user in users" :key="user.id">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        :id="'executor-' + user.id"
                                        :value="user.id"
                                        v-model="newTask.executors">
                                    <label class="form-check-label" :for="'executor-' + user.id">
                                        {{ user.full_name }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Rok izvršenja</label>
                            <input type="date" class="form-control" v-model="newTask.deadline">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Prioritet (1-10)</label>
                            <input type="number" class="form-control" v-model="newTask.priority" min="1" max="10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Grupa</label>
                            <select class="form-control" v-model="newTask.group_id">
                                <option v-for="group in groups" :value="group.id">{{ group.name }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Dodaj fajlove</label>
                            <input type="file" class="form-control" @change="handleFileUpload" multiple>

                            <ul class="mt-2" v-if="newTask.files.length">
                                <li v-for="(file, index) in newTask.files" :key="index">{{ file.name }}
                                    <button class="btn btn-sm btn-danger float-end" @click="removeFile(index, 'new')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="saveTask" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal za EDIT taska-->
        <div class="modal fade" id="editTaskModal" tabindex="-1" aria-labelledby="editTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editTaskModalLabel">Izmena zadatka</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Naslov</label>
                            <input type="text" class="form-control" v-model="editNewTask.title" maxlength="191" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Opis</label>
                            <textarea class="form-control" v-model="editNewTask.description" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Izvršioci</label>
                            <div class="border p-3 rounded">
                                <div class="form-check" v-for="user in users" :key="user.id">
                                    <input class="form-check-input"
                                        type="checkbox"
                                        :id="'executor-' + user.id"
                                        :value="user.id"
                                        v-model="editNewTask.executors">
                                    <label class="form-check-label" :for="'executor-' + user.id">
                                        {{ user.full_name }}
                                    </label>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Rok izvršenja</label>
                            <input type="date" class="form-control" v-model="editNewTask.deadline">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Prioritet (1-10)</label>
                            <input type="number" class="form-control" v-model="editNewTask.priority" min="1" max="10">
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Grupa</label>
                            <select class="form-control" v-model="editNewTask.group_id">
                                <option v-for="group in groups" :value="group.id">{{ group.name }}</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label required">Dodaj fajlove</label>
                            <input type="file" class="form-control" @change="handleFileUploadEdit" multiple>

                            <ul class="mt-2" v-if="editNewTask.files.length">
                                <label for="">Postojeći fajlovi</label>
                                <li v-for="(file, index) in editNewTask.files" :key="index">{{ file.name }} 
                                    <button class="btn btn-sm btn-danger ms-2" @click="removeFile(index, 'edit')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </li>
                            </ul>

                            <ul class="mt-2" v-if="editNewTask.new_files.length">
                                <label for="">Novi fajlovi</label>
                                <li v-for="(file, index) in editNewTask.new_files" :key="index">{{ file.name }} 
                                    <button class="btn btn-sm btn-danger ms-2" @click="removeFile(index, 'edit', 'newFiles')">
                                        <i class="fas fa-trash-alt"></i>
                                    </button>
                                </li>
                            </ul>
                        </div>
                        
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="saveEditTask" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal za DELETE taska-->
        <div class="modal fade" id="deleteTaskModal" tabindex="-1" aria-labelledby="deleteTaskModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="deleteTaskModalLabel">Brisanje zadatka</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Da li ste sigurni da želite da obrišete zadatak?</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="deleteTask" type="button" class="btn btn-warning" :disabled="loading">
                            {{ loading ? 'Sačekajte...' : 'Sačuvaj' }}
                        </button>
                    </div>
                </div>
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
                                <input v-model="taskStatus.status" class="form-check-input" type="radio" name="task_status" value="pending" id="pendingBtn">
                                <label class="form-check-label" for="pendingBtn">
                                    U radu
                                </label>
                            </div>
                            <div class="form-check">
                                <input v-model="taskStatus.status" class="form-check-input" type="radio" name="task_status" value="completed" id="completedBtn">
                                <label class="form-check-label" for="completedBtn">
                                    Završeno
                                </label>
                            </div>
                            <div class="form-check">
                                <input v-model="taskStatus.status" class="form-check-input" type="radio" name="task_status" value="cancelled" id="canceledBtn">
                                <label class="form-check-label" for="canceledBtn">
                                    Otkazano
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

        <!-- Modal za TIP korisnika-->
        <div v-if="currentUser.type === 'admin'" class="modal fade" id="editTypeModal" tabindex="-1" aria-labelledby="editTypeModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="editTypeModalLabel">Tip korisnika</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <div class="form-check">
                                <input v-model="userType.type" class="form-check-input" type="radio" name="user_type" value="admin" id="adminBtn">
                                <label class="form-check-label" for="adminBtn">
                                    Administrator
                                </label>
                            </div>
                            <div class="form-check">
                                <input v-model="userType.type" class="form-check-input" type="radio" name="user_type" value="manager" id="managerBtn">
                                <label class="form-check-label" for="managerBtn">
                                    Rukovodilac
                                </label>
                            </div>
                            <div class="form-check">
                                <input v-model="userType.type" class="form-check-input" type="radio" name="user_type" value="executor" id="executorBtn">
                                <label class="form-check-label" for="executorBtn">
                                    Izvršilac
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Odustani</button>
                        <button @click="saveType" type="button" class="btn btn-warning" :disabled="loading">
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
import { render } from 'vue';

export default {
    name: 'TaskAdministrationForm',
    data() {
        return {
            currentUser: window.currentUser || {},
            loading: false,
            newGroup: {
                name: '',
                description: '',
            },
            newGroupEdit: {
                id: null,
                name: '',
                description: '',
            },

            deleteGroupId: null,

            newTask: {
                title: '',
                description: '',
                executors: [],
                deadline: '',
                priority: 1,
                group_id: null,
                files: []
            },
            users: [],
            groups: [],

            deleteTaskId: null,
            editNewTask: {
                id: null,
                title: '',
                description: '',
                executors: [],
                deadline: '',
                priority: 1,
                group_id: null,
                files: [],
                new_files: []
            },
            
            taskStatus: {
                id: null,
                status: ''
            },

            taskComments: [],
            newComment: '',
            taskCommentsID: null,

            filters: {
                date_from: '',
                date_to: '',
                priority: '',
                executor_id: '',
                title: '',
            },
            users_filters: [],

            userType: {
                id: null,
                type: ''
            },
        };
    },
    methods: {
        groupsDataTable(){
            $('#groups-table').DataTable().clear().destroy();
            $('#groups-table').DataTable({
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
                    url: '/returnAllGroups',
                    type: "GET",
                },
                columnDefs: [
                    { targets: 0, orderable: true },
                    { targets: 1, orderable: true },
                    { targets: 2, orderable: false },
                    { targets: 3, orderable: false },
                ],
                columns: [
                    // { data: "id" },
                    {
                        data: "name",
                        className: "text-start"
                    },
                    {
                        data : 'description',
                        className: "text-start"
                    },
                    {
                        data: "created_by",
                        className: "text-start"
                    },
                    {
                        data: "akcije",
                        render: function (data, type, row) {
                            //ikonice ne rade 
                            return '<span class="dropdown float-end">'+
                                    '<a href="#" class="btn dropdown-toggle" data-bs-toggle="dropdown">'+
                                        '   <i class="fas fa-ellipsis-v"></i>'+
                                    ' </a>'+
                                    '<span class="dropdown-menu">' +
                                        '<a class="dropdown-item editGroupAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editGroupModal">'+
                                            '<i class="fas fa-edit me-2"></i>' + "Izmeni" +
                                        '</a>'+
                                        '<a class="dropdown-item deleteGroupAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#deleteGroupModal">'+
                                            '<i class="fas fa-trash me-2"></i>' + "Obriši" +
                                        '</a>'+
                                    '</span>'+
                                '</span>'
                        }
                    }
                ]
            });
        },
        saveGroup(){
            this.loading = true;

            if (this.newGroup.name === '') {
                alert('Naziv grupe je obavezan!');
                this.loading = false;
                return;
            }

            axios.post('/saveGroup', this.newGroup)
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.newGroup = { name: '', description: '' };
                    this.groupsDataTable(); 
                    this.getGroups();
                    $('#groupModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom pravljenja grupe:', error);
                });
        },
        editGroupAction() {
            $(document).on("click", ".editGroupAction", (e) => {
                const groupId = $(e.currentTarget).data('entry-id');
                axios.get(`/getGroup/${groupId}`)
                    .then(response => {
                        // console.log(response.data);
                        this.newGroupEdit = response.data;
                    })
                    .catch(error => {
                        console.error('Greška prilikom dobijanja podataka grupe:', error);
                    });
            });

        },
        editGroup() {
            this.loading = true;

            if (this.newGroupEdit.name === '') {
                alert('Naziv grupe je obavezan!');
                this.loading = false;
                return;
            }

            axios.post('/editGroup', this.newGroupEdit)
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.newGroupEdit = { id: null, name: '', description: '' };
                    this.groupsDataTable(); 
                    $('#editGroupModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom izmene grupe:', error);
                });
        },
        deleteGroupAction() {
            $(document).on("click", ".deleteGroupAction", (e) => {
                this.deleteGroupId = $(e.currentTarget).data('entry-id');
            });
        },
        deleteGroup() {
            const groupId = $(this).data('entry-id');
            this.loading = true;

            axios.post('/deleteGroup', { id: this.deleteGroupId })
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.deleteGroupId = null;
                    this.groupsDataTable(); 
                    $('#deleteGroupModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom brisanja grupe:', error);
                });
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
                    url: '/returnAllTasks',
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
                    { targets: 5, orderable: false },

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
                        data: "task_status",
                        className: "text-start",
                        orderable: false,
                        render: function (data, type, row){
                            if(data == "pending") return "U radu"
                            else if(data == "completed") return "Završeno"
                            else if(data == "cancelled") return "Otkazano"
                            else "Nije pronađeno"
                        }
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
                                        '<a class="dropdown-item editTaskAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editTaskModal">'+
                                            '<i class="fas fa-edit me-2"></i>' + "Izmeni" +
                                        '</a>'+
                                        '<a class="dropdown-item statusTaskAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#statusTaskModal">'+
                                            '<i class="fas fa-gears me-2"></i>' + "Status" +
                                        '</a>'+
                                        '<a class="dropdown-item commentTaskAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#commentTaskModal">'+
                                            '<i class="fas fa-comment me-2"></i>' + "Dodaj komentar" +
                                        '</a>'+
                                        '<a class="dropdown-item deleteTaskAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#deleteTaskModal">'+
                                            '<i class="fas fa-trash me-2"></i>' + "Obriši" +
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
        getUsers() {
            axios.get('/getUsers')
                .then(response => {
                    // console.log(response.data);
                    this.users = response.data.map(user => ({
                        id: user.id,
                        full_name: user.full_name
                    }));

                    this.users_filters = response.data.map(user => ({
                        id: user.id,
                        full_name: user.full_name
                    }));
                })
                .catch(error => {
                    console.error('Greška prilikom dobijanja korisnika:', error);
                });
        },
        getGroups() {
            axios.get('/getGroups')
                .then(response => {
                    // console.log(response.data);
                    this.groups = response.data.map(group => ({
                        id: group.id,
                        name: group.name
                    }));
                })
                .catch(error => {
                    console.error('Greška prilikom dobijanja grupa:', error);
                });
        },
        handleFileUpload(event) {
            //dodajemo fajlove a onda brišemo input da bismo dodali nove 
            const selectedFiles = Array.from(event.target.files);
            this.newTask.files = [...this.newTask.files, ...selectedFiles];
            event.target.value = null;
        },
        saveTask(){
            this.loading = true;

            //vaidacija 
            if (this.newTask.title === '') {
                alert('Naslov zadatka je obavezan!');
                this.loading = false;
                return;
            }

            if (this.newTask.executors.length === 0) {
                alert('Morate izabrati barem jednog izvršioca!');
                this.loading = false;
                return;
            }

            if (this.newTask.deadline === '') {
                alert('Rok izvršenja je obavezan!');
                this.loading = false;
                return;
            }

            if (this.newTask.priority < 1 || this.newTask.priority > 10) {
                alert('Prioritet mora biti između 1 i 10!');
                this.loading = false;
                return;
            }

            if (this.newTask.group_id === null) {
                alert('Morate izabrati grupu!');
                this.loading = false;
                return;
            }

            if (this.newTask.files.length === 0) {
                alert('Morate dodati barem jedan fajl!');
                this.loading = false;
                return;
            }
            
            // console.log(this.newTask);
            const formData = new FormData();
            formData.append('title', this.newTask.title);
            formData.append('description', this.newTask.description);
            formData.append('priority', this.newTask.priority);
            formData.append('deadline', this.newTask.deadline);
            formData.append('group_id', this.newTask.group_id);

            this.newTask.executors.forEach((id, index) => {
                formData.append(`executors[${index}]`, id);
            });

            this.newTask.files.forEach((file, index) => {
                formData.append('files[]', file);
            });

            axios.post('/saveTask', formData)
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.newTask = { title: '', description: '', executors: [], deadline: '', priority: 1, group_id: null, files: [] };
                    this.tasksDataTable(); 
                    $('#taskModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom pravljenja zadatka:', error);
                });
        },
        deleteTaskAction() {
            $(document).on("click", ".deleteTaskAction", (e) => {
                this.deleteTaskId = $(e.currentTarget).data('entry-id');
            });
        },
        deleteTask() {
            this.loading = true;
            // console.log(this.deleteTaskId);

            axios.post('/deleteTask', { id: this.deleteTaskId })
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.deleteTaskId = null;
                    this.tasksDataTable(); 
                    $('#deleteTaskModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom brisanja zadatka:', error);
                });
        },
        editTaskAction() {
            $(document).on("click", ".editTaskAction", (e) => {
                const taskId = $(e.currentTarget).data('entry-id');
                axios.get(`/getTask/${taskId}`)
                    .then(response => {
                        
                        console.log(response.data);
                        this.editNewTask = response.data;
                        this.editNewTask.executors = response.data.executors.map(executor => executor.executor_id);
                        this.editNewTask.files = response.data.files.map(file => ({
                            file_id: file.file_id,
                            name: file.file_name,
                        }));
                        this.editNewTask.new_files = [];
                    })
                    .catch(error => {
                        console.error('Greška prilikom dobijanja podataka zadatka:', error);
                    });
            });
        },
        removeFile(index, type, file_type = null) {
            if(type === 'new'){
                this.newTask.files.splice(index, 1);

            }
            else{
                if(file_type == "newFiles"){
                    this.editNewTask.new_files.splice(index, 1);
                }
                else {
                    this.editNewTask.files.splice(index, 1);
                }
            }
        },
        saveEditTask(){
            console.log(this.editNewTask);
            this.loading = true;

            // Validacija
            if (this.editNewTask.title === '') {
                alert('Naslov zadatka je obavezan!');
                this.loading = false;
                return;
            }

            if (this.editNewTask.executors.length === 0) {
                alert('Morate izabrati barem jednog izvršioca!');
                this.loading = false;
                return;
            }

            if (this.editNewTask.deadline === '') {
                alert('Rok izvršenja je obavezan!');
                this.loading = false;
                return;
            }

            if (this.editNewTask.priority < 1 || this.editNewTask.priority > 10) {
                alert('Prioritet mora biti između 1 i 10!');
                this.loading = false;
                return;
            }

            if (this.editNewTask.group_id === null) {
                alert('Morate izabrati grupu!');
                this.loading = false;
                return;
            }

            if (this.editNewTask.files.length === 0 && this.editNewTask.new_files.length === 0) {
                alert('Morate dodati barem jedan fajl!');
                this.loading = false;
                return;
            }

            const formData = new FormData();
            formData.append('id', this.editNewTask.id);
            formData.append('title', this.editNewTask.title);
            formData.append('description', this.editNewTask.description);
            formData.append('priority', this.editNewTask.priority);
            formData.append('deadline', this.editNewTask.deadline);
            formData.append('group_id', this.editNewTask.group_id);
            this.editNewTask.executors.forEach((id, index) => {
                formData.append(`executors[${index}]`, id);
            });
            this.editNewTask.files.forEach((file, index) => {
                // console.log(file);
                formData.append('files[]', file.file_id);
            });
             this.editNewTask.new_files.forEach((file, index) => {
                formData.append(`files_new[${index}]`, file);
            });

            axios.post('/editTask', formData)
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.editNewTask = { id: null, title: '', description: '', executors: [], deadline: '', priority: 1, group_id: null, files: [], new_files: [] };
                    this.tasksDataTable(); 
                    $('#editTaskModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom izmene zadatka:', error);
                });

        },
        handleFileUploadEdit(event) {
            //dodajemo fajlove a onda brišemo input da bismo dodali nove 
            const selectedFiles = Array.from(event.target.files);

            this.editNewTask.new_files = [...this.editNewTask.new_files, ...selectedFiles];
            event.target.value = null;
        },
        statusTaksAction(){
            $(document).on("click", ".statusTaskAction", (e) => {
                const taskId = $(e.currentTarget).data('entry-id');
                axios.get(`/getStatusTask/${taskId}`)
                    .then(response => {
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

            axios.post('/saveStatus', { taskStatus: this.taskStatus })
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
        commentTaskAction(){
            $(document).on("click", ".commentTaskAction", (e) => {
                this.taskCommentsID = $(e.currentTarget).data('entry-id');
                axios.get(`/getCommentTask/${this.taskCommentsID}`)
                    .then(response => {
                        console.log(response.data)
                        this.taskComments = response.data;
                    })
                    .catch(error => {
                        console.error('Greška prilikom dobijanja podataka o statusu zadatka:', error);
                    });
            });
        },
        commentTask(){
            // console.log(this.taskComments)
            this.loading = true;

            axios.post('/saveComments', { taskComments: this.taskComments, task_id:this.taskCommentsID })
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
        resetFilters() {
            this.filters = {
                date_from: '',
                date_to: '',
                priority: '',
                executor_id: '',
                title: '',
            };
            this.tasksDataTable();
        },
        applyFilters(){
            this.tasksDataTable();
        },
        usersDataTable(){
            $('#users-table').DataTable().clear().destroy();
            $('#users-table').DataTable({
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
                    url: '/returnAllUsers',
                    type: "GET",
                },
                columnDefs: [
                    { targets: 0, orderable: true },
                    { targets: 1, orderable: true },
                    { targets: 2, orderable: false },
                    { targets: 3, orderable: false },
                    { targets: 4, orderable: false },
                ],
                columns: [
                    // { data: "id" },
                    {
                        data : 'full_name',
                        className: "text-start"
                    },
                    {
                        data: "email",
                        className: "text-start"
                    },
                    {
                        data : 'username',
                        className: "text-start"
                    },
                    {
                        data: "type",
                        className: "text-start",
                        render: function (data, type, row) {
                            if(data === "admin") return "Administrator"
                            else if (data === "manager") return "Rukovodilac"
                            else if (data === "executor") return "Izvršilac"
                            else return "Nepoznat tip"
                        }
                    },
                    {
                        data: "akcije",
                        render: function (data, type, row) {
                            //ikonice ne rade 
                            return '<span class="dropdown float-end">'+
                                    '<a href="#" class="btn dropdown-toggle" data-bs-toggle="dropdown">'+
                                        '   <i class="fas fa-ellipsis-v"></i>'+
                                    ' </a>'+
                                    '<span class="dropdown-menu">' +
                                        '<a class="dropdown-item editTypeAction" data-entry-id="' + row.id + '" style="cursor: pointer;" data-bs-toggle="modal" data-bs-target="#editTypeModal">'+
                                            '<i class="fas fa-edit me-2"></i>' + "Promeni tip" +
                                        '</a>'+
                                    '</span>'+
                                '</span>'
                        }
                    }
                ]
            });
        },
        editTypeAction() {
            $(document).on("click", ".editTypeAction", (e) => {
                const taskId = $(e.currentTarget).data('entry-id');
                axios.get(`/getTypeUser/${taskId}`)
                    .then(response => {
                        
                        console.log(response.data);
                        this.userType = response.data;
                    })
                    .catch(error => {
                        console.error('Greška prilikom dobijanja podataka o tipu korisnika:', error);
                    });
            });
        },
        saveType(){
            this.loading = true;

            axios.post('/saveUserType', { userType: this.userType })
                .then(response => {
                    alert('Uspešno sačuvano!');
                    this.loading = false;
                    this.userType = { id: null, type: '' },
                    this.usersDataTable(); 
                    $('#editTypeModal').modal('hide');
                })
                .catch(error => {
                    this.loading = false;
                    console.error('Greška prilikom promene tipa korisnika:', error);
                });
        }
        
    },
    mounted() {
        this.groupsDataTable();
        this.editGroupAction();
        this.deleteGroupAction();

        this.tasksDataTable();
        this.getUsers();
        this.getGroups();
        this.deleteTaskAction();
        this.editTaskAction();
        this.statusTaksAction();
        this.commentTaskAction();

        this.usersDataTable();
        this.editTypeAction();
    
    }
};
</script>
<style>
.required::after {
    content: ' *';
    color: red;
}
</style>
