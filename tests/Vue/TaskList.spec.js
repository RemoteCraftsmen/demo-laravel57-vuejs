import {mount, createLocalVue} from '@vue/test-utils';
import expect from 'expect';
import Axios from 'axios';
import moxios from 'moxios';
import TaskList from '../../resources/js/components/TasksList';

const localVue = createLocalVue();
localVue.prototype.$http = Axios

describe('TodoList', () => {
    let wrapper;

    before(()=>{
        moxios.stubOnce('GET', '/api/tasks', {
            status: 200,
            response: {
                tasks: [
                    {
                        "id": 0,
                        "name": "task1",
                        "completed": 1,
                    },
                    {
                        "id": 1,
                        "name": "task2",
                        "completed": 0,
                    },
                    {
                        "id": 2,
                        "name": "task3",
                        "completed": 0,
                    }
                ]
            }
        });
    });

    beforeEach((done) => {
        moxios.install();
        
        wrapper = mount(TaskList, {
            localVue
        });
        
        moxios.wait(() => {
            wrapper.setData({
                loader: false,
            });
            done();
        });
    });

    afterEach(() => {
        moxios.uninstall();
    });

    it('renders a vue instance', () => {
        expect(wrapper.isVueInstance()).toBe(true);
    });

    it('presents tasks in table', () => {
        expect(wrapper.contains('table #datatable-responsive'));

        wrapper.vm.tasks.forEach((task) => {
            expect(wrapper.html()).toContain(task.name)
        })
    });

    it('presents loader when data is not loaded', () => {
        wrapper.setData({
            loader: true,
        });

        expect(wrapper.contains('.loader-container')).toBe(true);
    });

    it('present check circle icon when status of item is completed', () => {
        wrapper.setData({
            tasks: [
                {
                    "id": 0,
                    "name": "task1",
                    "completed": 1,
                },
            ]
        });

        expect(wrapper.find('td i.fa').classes()).toContain('fa-check-circle');
    });

    it('present empty circle icon when status of item is completed', () => {
        wrapper.setData({
            tasks: [
                {
                    "id": 0,
                    "name": "task1",
                    "completed": 0,
                },
            ]
        });

        expect(wrapper.find('td i.fa').classes()).toContain('fa-circle');
    });

    it('present "Add New" button', () => {
        expect(wrapper.find('button.btn.btn-success').text()).toBe('Add New');
    });

    it('can add new task after clicking "Add New" button and task has default parameters', (done) => {
        moxios.stubOnce('POST', '/api/tasks', {
            status: 200,
            response: {
                task: [
                    {
                        "id": 1,
                        "name": "addedTask",
                        "completed": 0,
                    },
                ]
            }
        });
        
        wrapper.setData({
            tasks: [
                {
                    "id": 0,
                    "name": "task1",
                    "completed": 0,
                },
            ]
        });

        const addingButton = wrapper.find('button.btn.btn-success');

        expect(wrapper.vm.tasks.length).toEqual(1);

        addingButton.trigger('click');

        moxios.wait(() => {
            expect(wrapper.vm.tasks.length).toEqual(2);
            expect(wrapper.html()).toContain('Please enter task name');
            expect(wrapper.findAll('td>i.fa').at(1).classes()).toContain('fa-circle');
            done();
        });
    });

    it('input shows after clicking on task name', () => {
        wrapper.setData({
            tasks: [
                {
                    "id": 0,
                    "name": "task1",
                    "completed": 0,
                },
            ]
        });
        
        expect(wrapper.find('input[name="name"]').isVisible()).toBe(false);

        wrapper.find('table>tbody>tr>td>span').trigger('click');

        expect(wrapper.find('input[name="name"]').isVisible()).toBe(true);
    });

    it('can update task after clicking its name', async () => {
        moxios.stubOnce('PATCH', '/api/tasks/0', {
            status: 200,
            response: {
                status: true,
                updated: true
            }
        });
        
        wrapper.setData({
            tasks: [
                {
                    "id": 0,
                    "name": "task1",
                    "completed": 0,
                },
            ]
        });

        expect(wrapper.html()).toContain('task1');

        await wrapper.vm.$nextTick();

        const input = wrapper.find('input[name="name"]');
        
        input.setValue("updated todo");
        input.trigger('change');

        await moxios.wait;

        expect(wrapper.html()).toContain('updated todo');
    });

    it('can update status of task after clicking its icon', (done) => {
        moxios.stubOnce('PATCH', '/api/tasks/0/complete', {
            status: 200,
            response: {
                status: true,
                completed: true
            }
        });
        
        wrapper.setData({
            tasks: [
                {
                    id: 0,
                    name: "task1",
                    completed: 0,
                },
            ]
        });

        expect(wrapper.html()).toContain('fa-circle');

        const statusIcon = wrapper.find('.status-row');

        statusIcon.trigger('click');

        moxios.wait(async () => {
            await wrapper.vm.$forceUpdate();
            expect(wrapper.html()).toContain('fa-check-circle');
            done();
        });
    });
});
