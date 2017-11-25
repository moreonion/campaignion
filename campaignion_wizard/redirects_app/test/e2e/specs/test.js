// For authoring Nightwatch tests, see
// http://nightwatchjs.org/guide#usage

function listSelectors (selector, length) {
  var arr = []
  for (var i = 1; i <= length; i++) {
    arr.push(selector + ':nth-of-type(' + i + ')')
  }
  return arr
}

module.exports = {
  'app is being rendered': function (browser) {
    var app = browser.page.app()

    app.navigate()

    app.expect.element('@app').to.be.visible
    app.expect.element('@addRedirect').to.be.visible
    app.expect.section('@redirectList').to.be.visible
    app.expect.element('@destinationInput').to.be.visible
    app.expect.element('@destinationDropdown').not.to.be.present
    app.expect.section('@dialog').not.to.be.visible
  },

  'app loads initial data': function (browser) {
    var app = browser.page.app()
    var redirectList = app.section.redirectList
    var redirect = redirectList.section.redirect
    var redirectSelectors = listSelectors(redirect.selector, 2)

    browser.assert.elementCount(redirect.selector, 2)

    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-label').text.to.be.equal('My internal label')
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-info-filters li:nth-of-type(1)').text.to.be.equal('Supporter has opted in')
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-info-filters li:nth-of-type(2)').text.to.be.equal('First name contains foo')
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-destination').text.to.contain('Pretty title of my node (20)')

    browser.expect.element(redirectSelectors[1] + ' .pra-redirect-label').text.to.be.equal('Spam haters go here')
    browser.expect.element(redirectSelectors[1] + ' .pra-redirect-info-filters li:nth-of-type(1)').text.to.be.equal('Supporter hasn’t opted in')
    browser.expect.element(redirectSelectors[1] + ' .pra-redirect-destination').text.to.contain('http://opt-in.com')

    app.expect.element('@destinationInput').value.to.be.equal('http://example.com')
  },

  'app allows going back when nothing has changed': function (browser) {
    var app = browser.page.app()
    app.click('@back')
    browser.getAlertText(function (result) {
      browser.expect(result.value).to.be.equal('You can leave the page now.')
    })
    browser.acceptAlert()
  },

  'app allows submitting when nothing has changed': function (browser) {
    var app = browser.page.app()
    app.click('@submit')
    browser.getAlertText(function (result) {
      browser.expect(result.value).to.be.equal('You can leave the page now.')
    })
    browser.acceptAlert()
  },

  'app warns on going back when the default destination was changed': function (browser) {
    var app = browser.page.app()
    var msgBox = app.section.messageBox
    app.clearValue('@destinationInput').setValue('@destinationInput', '/something_else')
    app.click('@back')

    app.expect.section('@messageBox').to.be.visible
    msgBox.expect.element('@box').to.be.visible
    msgBox.expect.element('@title').to.be.visible
    msgBox.expect.element('@title').text.to.be.equal('Unsaved changes')
    msgBox.expect.element('@message').to.be.visible
    msgBox.expect.element('@message').text.to.contain('You will lose your changes if you go back.')
    msgBox.expect.element('@cancel').to.be.visible
    msgBox.expect.element('@ok').to.be.visible

    msgBox.click('@cancel')
    browser.getAlertText(function (result) {
      browser.expect(result.value).to.be.equal('Just stay here for a moment.')
    })
    browser.acceptAlert()
  },

  'redirect drag’n’drop': function (browser) {
    // In chromium this test regularly fails, probably due to a selenium issue.
    // Workaround: After the chromium window opened, move the window a little bit,
    // place the mouse pointer somewhere inside the window.
    console.log('If this test keeps failing, try to move the browser window a little and place the mouse pointer inside.')
    var app = browser.page.app()
    var redirect = app.section.redirectList.section.redirect
    var redirectSelectors = listSelectors(redirect.selector, 2)

    browser
      .pause(500)
      .moveToElement(redirectSelectors[1] + ' .pra-redirect-handle', 5, 5)
      .mouseButtonDown(0)
      .moveToElement(redirectSelectors[0] + ' .pra-redirect-handle', 5, 5)
      .mouseButtonUp(0)
      .pause(500)

    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-label').text.to.be.equal('Spam haters go here')
    browser.expect.element(redirectSelectors[1] + ' .pra-redirect-label').text.to.be.equal('My internal label')
  },

  'edit a redirect': function (browser) {
    var app = browser.page.app()
    var redirect = app.section.redirectList.section.redirect
    var redirectSelectors = listSelectors(redirect.selector, 3)
    var dialog = app.section.dialog
    var filterEditor = dialog.section.filterEditor
    var filterList = filterEditor.section.filterList

    browser.assert.elementCount(redirect.selector, 2)

    browser.pause(500)
    browser.click(redirectSelectors[0] + ' ' + redirect.elements.edit.selector)
    app.waitForElementVisible(app.section.dialog.selector, 1000)

    dialog.expect.element('@title').text.to.be.equal('Edit Spam haters go here')
    dialog.expect.element('@label').value.to.be.equal('Spam haters go here')
    filterList.expect.section('@optInFilter').to.be.visible
    filterList.section.optInFilter.expect.element('@value').value.to.be.equal('has not')
    filterList.expect.section('@fieldFilter').not.to.be.present
    dialog.expect.element('@destinationInput').value.to.be.equal('http://opt-in.com')
    dialog.expect.element('@save').not.to.have.attribute('disabled')

    // change the label
    dialog.clearValue('@label').setValue('@label', 'Spam lovers go here')
    browser.pause(500) // let vue catch up with rendering
    dialog.expect.element('@title').text.to.be.equal('Edit Spam lovers go here')

    // add a submission field filter
    filterEditor.click('@addFilter')
    app.waitForElementVisible('@dropdown', 1000)
    app.click(app.elements.dropdown.selector + ' li:nth-of-type(2)')
    filterList.waitForElementVisible(filterList.section.fieldFilter.selector, 1000)
    filterList.assert.elementCount(filterList.selector + ' li.pra-filter', 2)
    // set field to 'last name'
    filterList.section.fieldFilter.click('@field')
    app.waitForElementVisible('@selectDropdown', 1000)
    app.click(app.elements.selectDropdown.selector + ' li:nth-of-type(2)')
    app.waitForElementNotVisible('@selectDropdown', 1000)
    // set operator to 'doesn’t contain'
    filterList.section.fieldFilter.click('@operator')
    app.waitForElementVisible('@selectDropdown', 1000)
    app.click(app.elements.selectDropdown.selector + ' li:nth-of-type(4)')
    app.waitForElementNotVisible('@selectDropdown', 1000)
    filterList.section.fieldFilter.setValue('@value', 'bar')

    // delete the opt-in fiter
    filterList.section.optInFilter.click('@remove')
    filterList.waitForElementNotPresent(filterList.section.optInFilter.selector, 1000)

    dialog.click('@save')
    browser.waitForElementNotVisible(app.section.dialog.selector, 1000)

    browser.assert.elementCount(redirect.selector, 2)
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-label').text.to.be.equal('Spam lovers go here')
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-info-filters li:nth-of-type(1)').text.to.be.equal('Last name doesn’t contain bar')
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-destination').text.to.contain('http://opt-in.com')
  },

  'adding a new redirect': function (browser) {
    var app = browser.page.app()
    var redirect = app.section.redirectList.section.redirect
    var redirectSelectors = listSelectors(redirect.selector, 3)
    var dialog = app.section.dialog
    var filterEditor = dialog.section.filterEditor
    var filterList = filterEditor.section.filterList

    browser.assert.elementCount(redirect.selector, 2)

    browser.pause(500) // work around nightwatch click issue
    app.click('@addRedirect')
    app.waitForElementVisible(app.section.dialog.selector, 1000)

    dialog.expect.element('@title').text.to.be.equal('Add personalized redirect')
    dialog.expect.element('@label').value.to.be.equal('')
    filterList.expect.section('@optInFilter').not.to.be.present
    filterList.expect.section('@fieldFilter').not.to.be.present
    dialog.expect.element('@destinationInput').value.to.be.equal('')
    dialog.expect.element('@save').to.have.attribute('disabled')

    dialog.setValue('@label', 'Awesome redirect')
    dialog.setValue('@destinationInput', 'foo')
    dialog.waitForElementVisible('@destinationDropdown', 1000)

    // choose the third item from the dropdown
    dialog.click(dialog.elements.destinationDropdown.selector + ' li:nth-of-type(3)')
    dialog.waitForElementNotPresent('@destinationDropdown', 1000)
    dialog.expect.element('@destinationInput').value.to.equal('Some Node title containing foo (3)')

    // add an opt-in filter
    browser.pause(300) // work around nightwatch click issue
    filterEditor.click('@addFilter')
    app.waitForElementVisible('@dropdown', 1000)
    app.click(app.elements.dropdown.selector + ' li:nth-of-type(1)')
    filterList.waitForElementVisible(filterList.section.optInFilter.selector, 1000)
    filterList.assert.elementCount(filterList.section.optInFilter.selector, 1)
    filterList.section.optInFilter.click('@value')
    app.waitForElementVisible('@selectDropdown', 1000)
    app.click(app.elements.selectDropdown.selector + ' li:nth-of-type(2)')
    app.waitForElementNotVisible('@selectDropdown', 1000)

    dialog.click('@save')

    browser.waitForElementNotVisible(app.section.dialog.selector, 1000)
    browser.assert.elementCount(redirect.selector, 3)
    browser.expect.element(redirectSelectors[2] + ' .pra-redirect-label').text.to.be.equal('Awesome redirect')
    browser.expect.element(redirectSelectors[2] + ' .pra-redirect-info-filters li:nth-of-type(1)').text.to.be.equal('Supporter hasn’t opted in')
    browser.expect.element(redirectSelectors[2] + ' .pra-redirect-destination').text.to.contain('Some Node title containing foo (3)')
  },

  'duplicate a redirect': function (browser) {
    var app = browser.page.app()
    var redirect = app.section.redirectList.section.redirect
    var redirectSelectors = listSelectors(redirect.selector, 4)
    var dialog = app.section.dialog
    var filterEditor = dialog.section.filterEditor
    var filterList = filterEditor.section.filterList

    browser.assert.elementCount(redirect.selector, 3)

    browser.pause(500)
    browser.click(redirectSelectors[0] + ' ' + redirect.elements.openDropdown.selector) // Open dropdown
    app.waitForElementVisible('@dropdown', 1000)
    app.click(app.elements.dropdown.selector + ' li:first-of-type') // Duplicate
    app.waitForElementNotVisible('@dropdown', 1000)
    app.waitForElementVisible(app.section.dialog.selector, 1000)

    dialog.expect.element('@title').text.to.be.equal('Add personalized redirect')
    dialog.expect.element('@label').value.to.be.equal('Copy of Spam lovers go here')
    filterList.expect.section('@fieldFilter').to.be.visible
    filterList.expect.section('@optInFilter').not.to.be.present
    dialog.expect.element('@destinationInput').value.to.be.equal('http://opt-in.com')
    dialog.expect.element('@save').not.to.have.attribute('disabled')

    // change the label
    dialog.clearValue('@label').setValue('@label', 'Real spam lovers go here')

    dialog.click('@save')

    browser.waitForElementNotVisible(app.section.dialog.selector, 1000)
    browser.assert.elementCount(redirect.selector, 4)
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-label').text.to.be.equal('Spam lovers go here')
    browser.expect.element(redirectSelectors[1] + ' .pra-redirect-label').text.to.be.equal('My internal label')
    browser.expect.element(redirectSelectors[2] + ' .pra-redirect-label').text.to.be.equal('Awesome redirect')
    browser.expect.element(redirectSelectors[3] + ' .pra-redirect-label').text.to.be.equal('Real spam lovers go here')
  },

  'delete a redirect': function (browser) {
    var app = browser.page.app()
    var redirect = app.section.redirectList.section.redirect
    var redirectSelectors = listSelectors(redirect.selector, 4)
    var msgBox = app.section.messageBox

    browser.assert.elementCount(redirect.selector, 4)

    browser.pause(500)
    browser.click(redirectSelectors[0] + ' ' + redirect.elements.openDropdown.selector) // Open dropdown
    app.waitForElementVisible('@dropdown', 1000)
    app.click(app.elements.dropdown.selector + ' li:last-of-type') // Delete
    app.waitForElementNotVisible('@dropdown', 1000)

    app.expect.section('@messageBox').to.be.visible
    msgBox.expect.element('@box').to.be.visible
    msgBox.expect.element('@title').to.be.visible
    msgBox.expect.element('@title').text.to.be.equal('Remove redirect?')
    msgBox.expect.element('@message').to.be.visible
    msgBox.expect.element('@message').text.to.contain('Spam lovers go here')
    msgBox.expect.element('@cancel').to.be.visible
    msgBox.expect.element('@ok').to.be.visible

    browser.pause(300)
    msgBox.click('@ok')

    browser.pause(500)
    app.expect.section('@messageBox').not.to.be.visible
    browser.assert.elementCount(redirect.selector, 3)
    browser.expect.element(redirectSelectors[0] + ' .pra-redirect-label').text.to.be.equal('My internal label')
    browser.expect.element(redirectSelectors[1] + ' .pra-redirect-label').text.to.be.equal('Awesome redirect')
    browser.expect.element(redirectSelectors[2] + ' .pra-redirect-label').text.to.be.equal('Real spam lovers go here')
  },

  'save redirects to server': function (browser) {
    // browser.end()
  }
}
