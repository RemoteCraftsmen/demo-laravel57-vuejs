<template>
    <div class="loader-container" v-if="loader">
        <div class="lds-ripple">
            <div></div>
            <div></div>
        </div>
    </div>
    <div v-else>
        <div class="col-md-5 col-sm-5 col-xs-12 form-group pull-right top_search">
            <button
                    @click="add"
                    type="submit"
                    class="btn btn-success pull-right">
                <i class="fa fa-plus"></i>
                Add New
            </button>
        </div>
        <confirmation-modal
                @confirmed="confirmed"
                :message="'Are you sure you want to delete this estimate?'">
        </confirmation-modal>
        <table id="datatable-responsive" class="table table-striped table-bordered dt-responsive nowrap text-center"
               cellspacing="0" width="100%">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Completed</th>
                <th>Action</th>
            </tr>
            </thead>
            <tbody>
            <tr v-for="(task, index) in tasks" v-bind:index="index">
                <td>{{index+1}}</td>
                <td>
                    <span v-show="!editing" @click="edit(index)">{{ task.name ? task.name : 'Please enter task name' }}</span>
                    <span v-show="editing && editingIndex !== index" @click="edit(index)">{{ task.name ? task.name : 'Please enter task name' }}</span>
                    <input 
                        v-show="editing && editingIndex === index" 
                        class="col-sm-12 invisible-center"
                        type="text"
                        name="name"
                        v-model="task.name" :ref="'title'"
                        placeholder="Please enter task name"
                        @change="save(task, index, $event)"
                        @blur="edit(index)"
                    >
                </td>
                <td class="status-row" v-on:click="changeStatus(task, index)">
                    <i v-if="task.completed" class="fa fa-check-circle"></i>
                    <i v-else class="fa fa-circle"></i>
                </td>
                <td>
                    <a data-toggle="modal" data-target="#confirm" @click="pendingDelete(task, index)"><i
                            class="fa fa-trash"></i></a>
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</template>
<script>
    import ConfirmationModal from './ConfirmationModal.vue';

    export default {
        components: {ConfirmationModal},
        data() {
            return {
                loader: true,
                tasks: [],
                editing: false,
                editingIndex: null,
                task: {},
            }
        },
        mounted() {
            this.$http.get('/api/tasks').then((response) => {
                this.tasks = response.data.tasks;
                this.loader = false;
            })
        },
        methods: {
            add() {
                this.$http.post('/api/tasks', { name : 'Please enter task name' }).then((response) => {
                    this.tasks.push(response.data.task);
                })
            },
            destroy(task, index) {
                this.$http.delete('/api' + task.path, task.id)
                    .then(
                        () => {
                            this.tasks.splice(index, 1);
                            this.$forceUpdate();
                        },
                        error => {
                            alert(error);
                        });
            },
            confirmed() {
                this.destroy(this.task, this.index);
            },
            pendingDelete(task, index) {
                this.task = task;
                this.index = index;
                this.$forceUpdate();
            },
            edit(index) {
                this.editingIndex = index;
                this.editing = !this.editing;
                this.$nextTick(() => {
                    let input = this.$refs.title[index];
                    input.focus();
                });
            },
            save(task, index, event) {
                task.column = event.target.name;
                this.$http.patch('/api/tasks/' + task.id, task)
                    .then(
                        () => {
                            this.$nextTick(() => {
                                let input = this.$refs.title[index];
                                input.blur();
                            });
                        },
                        error => {
                            alert(error);
                        }
                    );
            },
            changeStatus(task) {
                this.$http.patch('/api/tasks/' + task.id + '/complete', task)
                    .then(
                        response => {
                            task.completed = response.data.completed;
                            this.$forceUpdate();
                        },
                        error => {
                            alert(error);
                        }
                    );
            },
        }
    }
</script>
