<template>
  <div id="app">
    <img src="./assets/logo.png">
    <hello></hello>
    <el-dropdown>
      <span class="el-dropdown-link">
        Dropdown List<i class="el-icon-caret-bottom el-icon--right"></i>
      </span>
      <el-dropdown-menu slot="dropdown">
        <el-dropdown-item>Action 1</el-dropdown-item>
        <el-dropdown-item>Action 2</el-dropdown-item>
        <el-dropdown-item>Action 3</el-dropdown-item>
        <el-dropdown-item disabled>Action 4</el-dropdown-item>
        <el-dropdown-item divided>Action 5</el-dropdown-item>
      </el-dropdown-menu>
    </el-dropdown>

    Select with localized placeholder:
    <el-select>
      <el-option>foo</el-option>
      <el-option>bar</el-option>
    </el-select>

  </div>
</template>

<script>
import Hello from './components/Hello'

export default {
  name: 'app',
  components: {
    Hello
  }
}
</script>

<style>
#app {
  font-family: 'Avenir', Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  text-align: center;
  color: #2c3e50;
  margin-top: 60px;
}
</style>
