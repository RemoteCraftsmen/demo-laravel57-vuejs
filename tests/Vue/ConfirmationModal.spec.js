import {mount} from '@vue/test-utils';
import expect from 'expect';
import ConfirmationModal from '../../resources/js/components/ConfirmationModal';

describe('ConfirmationModal', () => {
    let wrapper;
    beforeEach(() => {
        wrapper = mount(ConfirmationModal);
    });

    it('renders a vue instance', () => {
        expect(wrapper.isVueInstance()).toBe(true);
    });

    it('display text with question', () => {
        wrapper.setProps({message: "Are you sure you want to delete this estimate?"});

        expect(wrapper.html()).toContain('Are you sure you want to delete this estimate?');
    });

    it('have three buttons ', () => {
        expect(wrapper.findAll('button').length).toBe(3);
    });

    it('emits "confirm" event after clicking acceptance button', () => {
        wrapper.find('button.btn-success').trigger('click');

        expect(wrapper.emitted('confirmed')).toBeTruthy();
    })
})
