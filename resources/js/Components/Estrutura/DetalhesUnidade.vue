<template>
  <div class="w-full h-[255px] relative bg-white rounded-[20px] p-4">
    <div class="relative w-full h-full">
      <!-- ID and CNPJ section -->
      <div
        class="flex justify-between items-center absolute left-0 top-[50px] w-full"
      >
        <div
          class="w-[135px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
        >
          <div class="w-6 h-6 rounded-full">
            <img
              src="/storage/images/storefront-bleck.svg"
              alt=""
              class="inline-block"
            />
          </div>
          <div
            class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
          >
            ID #0003
          </div>
        </div>
        <div
          class="w-[219px] h-[43px] px-2.5 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
        >
          <div class="w-6 h-6 rounded-full">
            <img
              src="/storage/images/assured_workload.svg"
              alt=""
              class="inline-block"
            />
          </div>
          <div
            class="text-[#262a27] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
          >
            54.007.283/0001-22
          </div>
        </div>
      </div>

      <!-- Unit Name -->
      <div
        class="absolute left-0 top-0 text-[#262a27] text-[28px] font-bold font-['Figtree'] leading-[34px] tracking-tight"
      >
        Ariquemes - centro
      </div>

      <!-- Unit Owner Section -->
      <div class="absolute bottom-0 left-0 w-full h-[67px] flex flex-col gap-1">
        <div
          class="text-[#6d6d6d] text-[15px] font-semibold font-['Figtree'] leading-tight"
        >
          Esta unidade pertence a
        </div>
        <div
          class="h-[43px] pl-4 pr-3 py-3 bg-[#f3f8f3] rounded-lg flex justify-between items-center"
        >
          <div
            class="text-[#6db631] text-base font-semibold font-['Figtree'] leading-[13px] tracking-tight"
          >
            Diego Rosa
          </div>
          <div class="w-4 h-4 bg-[#d9d9d9] rounded-full"></div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue';

const props = defineProps({
  unidade: {
    type: Object,
    required: false,
    default: null,
  },
});
</script>

<style scoped>
/* Custom styles to ensure the layout works responsively */
@media (max-width: 640px) {
  .text-[28px] {
    font-size: 22px;
  }
  .text-[15px] {
    font-size: 14px;
  }
  .w-[388px] {
    width: 100%;
  }
  .w-[219px] {
    width: 100%;
  }
  .w-[135px] {
    width: 100%;
  }
}
</style>
