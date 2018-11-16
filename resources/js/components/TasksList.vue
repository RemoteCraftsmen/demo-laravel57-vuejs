<template>
    <div>
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
                    <span v-show="!editing" @click="edit(index)">{{ task.name ? task.name : 'Enter task' }}</span>
                    <span v-show="editing && editingIndex !== index" @click="edit(index)">{{ task.name ? task.name : 'Enter task' }}</span>
                    <input v-show="editing && editingIndex === index" class="col-sm-12 invisible-center" type="text"
                           name="name"
                           v-model="task.name" :ref="'title'"
                           placeholder="Enter task" @change="save(task, $event)" @blur="edit(index)">
                </td>
                <td v-on:click="changeStatus(task, index)">
                    <i v-if="task.completed" class="fas fa-check-circle"></i>
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
        props: ['tasks'],
        data() {
            return {
                editing: false,
                editingIndex: null,
                task: {},
            }
        },
        methods: {
            destroy(task, index) {
                axios.delete('api' + task.path, task.id)
                    .then(() => {
                            this.tasks.splice(index, 1);
                            this.$forceUpdate();
                        },
                        (error) => {
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
            save(task, event) {
                task.column = event.target.name;
                axios.patch('api/tasks/update/column/' + task.id, task)
                    .then(
                        (error) => {
                            alert(error);
                        }
                    );
            },
            changeStatus(task) {
                axios.patch('api/tasks/complete/' + task.id, task)
                    .then(response => {
                            task.completed = response.data.completed;
                            this.$forceUpdate();
                        },
                        (error) => {
                            alert(error);
                        }
                    );
            },
        }
    }
</script>
