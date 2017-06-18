/* global describe, it, before */

import chai from 'chai';
import campVue from '../../js/campaignion_vue.js';

const expect = chai.expect;

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

});
