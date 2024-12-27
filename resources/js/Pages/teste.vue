<template>
  <div class="container">
    <div class="info-box">
      <div class="info-card">
        <div class="icon-wrapper">
          <div class="icon-bg"></div>
          <div class="icon"></div>
        </div>
        <span class="info-text">ID #0003</span>
      </div>
      <div class="info-card">
        <div class="icon-wrapper">
          <div class="icon-bg"></div>
          <div class="icon"></div>
        </div>
        <span class="info-text">54.007.283/0001-22</span>
      </div>
      <h1 class="title">Ariquemes - centro</h1>
      <div class="details-box">
        <span class="details-title">Esta unidade pertence a</span>
        <div class="owner-card">
          <span class="owner-name">Diego Rosa</span>
          <div class="owner-icon">
            <div class="triangle"></div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  width: 100%;
  height: 100%;
  position: relative;
  background: white;
  border-radius: 20px;
}
.info-box {
  width: 388px;
  height: 197px;
  position: absolute;
  top: 26px;
  left: 48px;
}
.info-card {
  width: fit-content;
  height: 43px;
  padding: 12px;
  margin-top: 8px;
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  background: #f3f8f3;
  border-radius: 8px;
}
.icon-wrapper {
  width: 24px;
  height: 24px;
  position: relative;
}
.icon-bg {
  width: 24px;
  height: 24px;
  background: #d9d9d9;
  position: absolute;
}
.icon {
  width: 20px;
  height: 18px;
  background: #1c1b1f;
  position: absolute;
  top: 3px;
  left: 2px;
}
.info-text {
  font-family: Figtree, sans-serif;
  font-size: 16px;
  font-weight: 600;
  color: #262a27;
  margin-left: 8px;
}
.title {
  font-family: Figtree, sans-serif;
  font-size: 28px;
  font-weight: 700;
  color: #262a27;
  margin-top: 16px;
}
.details-box {
  margin-top: 20px;
}
.details-title {
  font-family: Figtree, sans-serif;
  font-size: 15px;
  font-weight: 600;
  color: #6d6d6e;
}
.owner-card {
  display: inline-flex;
  align-items: center;
  justify-content: space-between;
  background: #f3f8f3;
  border-radius: 8px;
  padding: 12px 16px;
}
.owner-name {
  font-family: Figtree, sans-serif;
  font-size: 16px;
  font-weight: 600;
  color: #6db631;
}
.owner-icon {
  width: 16px;
  height: 16px;
  position: relative;
}
.triangle {
  width: 8px;
  height: 4px;
  border: 1.6px solid #6db631;
  position: absolute;
  top: 6px;
  left: 4px;
}
</style>
