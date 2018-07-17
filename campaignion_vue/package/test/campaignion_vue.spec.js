/* global describe, it, before */

import chai from 'chai';

const expect = chai.expect;
const MockBrowser = require('mock-browser').mocks.MockBrowser;

// Mock globals for element-ui.
global.window = new MockBrowser().getWindow();
global.document = global.window.document;
global.navigator = global.window.navigator;

const campVue = require('../../js/campaignion_vue.js');

describe('campaignion_vue', () => {

  it('provides a Vue function', () => {
    expect(typeof campVue.Vue).to.be.equal('function');
  });

  describe('campaignion_vue.Vue', () => {
    it('generates a Vue instance', () => {
      var vm = new campVue.Vue();

      expect(vm._isVue).to.be.equal(true);
    });
  });

  it('provides an axios object', () => {
    expect(typeof campVue.axios).to.be.equal('function');
    expect(typeof campVue.axios.get).to.be.equal('function');
  });

  it('provides a Vuex object', () => {
    expect(typeof campVue.Vuex).to.be.equal('object');
    expect(typeof campVue.Vuex.Store).to.be.equal('function');
  });

  it('provides element-ui components', () => {
    expect(campVue.element.Button).to.exist;
    expect(campVue.element.Col).to.exist;
    expect(campVue.element.Collapse).to.exist;
    expect(campVue.element.CollapseItem).to.exist;
    expect(campVue.element.CollapseTransition).to.exist;
    expect(campVue.element.Dialog).to.exist;
    expect(campVue.element.Dropdown).to.exist;
    expect(campVue.element.DropdownMenu).to.exist;
    expect(campVue.element.DropdownItem).to.exist;
    expect(campVue.element.Form).to.exist;
    expect(campVue.element.FormItem).to.exist;
    expect(campVue.element.Input).to.exist;
    expect(campVue.element.MessageBox).to.exist;
    expect(campVue.element.Option).to.exist;
    expect(campVue.element.Popover).to.exist;
    expect(campVue.element.Radio).to.exist;
    expect(campVue.element.RadioGroup).to.exist;
    expect(campVue.element.Row).to.exist;
    expect(campVue.element.Select).to.exist;
    expect(campVue.element.Switch).to.exist;
  });

  it('provides element-ui locale', () => {
    expect(typeof campVue.elementLocale).to.be.equal('object');
  });

  it('provides vuedraggable', () => {
    expect(typeof campVue.draggable).to.be.equal('object');
  });

});
