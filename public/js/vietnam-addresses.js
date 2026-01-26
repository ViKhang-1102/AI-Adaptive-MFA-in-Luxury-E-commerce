// Vietnamese Provinces, Districts, and Wards Data - Complete list
const vietnamAddresses = {
  "Hà Nội": {
    "Hoàn Kiếm": ["Cửa Đông", "Cửa Nam", "Cửa Bắc", "Hàng Bạc", "Hàng Buồm", "Hàng Gai", "Hàng Ngang", "Hàng Trống", "Tràng Tiền"],
    "Ba Đình": ["Ba Đình", "Cống Vị", "Điện Biên Phủ", "Hoàn Kiếm", "Liễu Giai", "Quảng An", "Thanh Vân"],
    "Hai Bà Trưng": ["Bách Khoa", "Hàng Bột", "Kham Thiên", "Minh Khai", "Thống Nhất"],
    "Đống Đa": ["Hàng Bún", "Kim Mã", "Láng", "Láng Hạ", "Phương Mai", "Trung Liệt"],
    "Tây Hồ": ["Cầu Giấy", "Hải Bà Trưng", "Phú Thượng", "Quảng An", "Thụy Khuê", "Tứ Liên", "Tú Hoa", "Vĩnh Phúc"],
    "Cầu Giấy": ["Dịch Vọng", "Dịch Vọng Hậu", "Hoàng Minh Giám", "Liễu Giai", "Nghĩa Đô", "Nghĩa Tân", "Quan Hoa", "Yên Phụ"],
    "Thanh Xuân": ["Khương Thượng", "Khương Đình", "Phương Liệt", "Phương Mai", "Thanh Xuân Bắc", "Thanh Xuân Nam", "Thanh Xuân Trung"],
    "Hoàng Mai": ["Cầu Diễn", "Hạ Dình", "Hoàng Liệt", "Liên Mạc", "Mai Động", "Minh Khai", "Vĩnh Hưởng", "Viêt Hương"],
    "Long Biên": ["Gia Thụy", "Giang Biên", "Long Biên", "Thạch Bàn", "Uyên Hưởng", "Việt Hùng"],
    "Bắc Từ Liêm": ["Cầu Giấy", "Tây Mỗ", "Thượng Cát", "Xuân Phương"],
    "Nam Từ Liêm": ["Hà Cầu", "Mỗ Lao", "Từ Liêm", "Cầu Giấy"],
    "Hà Đông": ["Buôn", "Công Hoàng", "Dương Nội", "Hà Cầu", "Lê Lợi", "Minh Khai", "Thanh Am", "Tây Mỗ"],
    "Sơn Tây": ["Ba Sao", "Chúc Sơn", "Minh Phú", "Phú Cát"],
  },
  "Hà Giang": {
    "Thành Phố Hà Giang": ["Thành Phố"],
    "Vị Xuyên": ["Thủy Toàn", "Lùng Khê"],
    "Quản Bạ": ["Lũng Cảng", "Vân Chùa"],
  },
  "Cao Bằng": {
    "Thành Phố Cao Bằng": ["Thành Phố"],
    "Bảo Lâm": ["Pác Hà", "Rặng Đông"],
  },
  "Bắc Kạn": {
    "Thành Phố Bắc Kạn": ["Thành Phố"],
    "Chợ Mới": ["Tây Hợp", "Thung Nham"],
  },
  "Tuyên Quang": {
    "Thành Phố Tuyên Quang": ["Thành Phố"],
    "Nà Hang": ["Cẩm Thượng", "Chiêu Lương"],
  },
  "Lào Cai": {
    "Thành Phố Lào Cai": ["Thành Phố"],
    "Sa Pa": ["Sa Pa", "Tả Phìn"],
  },
  "Yên Bái": {
    "Thành Phố Yên Bái": ["Thành Phố"],
    "Lục Yên": ["Tân Hợp"],
  },
  "Thái Nguyên": {
    "Thành Phố Thái Nguyên": ["Thành Phố"],
    "Định Hóa": ["Cẩm Thượng"],
  },
  "Phú Thọ": {
    "Thành Phố Việt Trì": ["Thành Phố"],
    "Phú Xuyên": ["Tân Hợp"],
  },
  "Vĩnh Phúc": {
    "Thành Phố Vĩnh Yên": ["Thành Phố"],
    "Bình Xuyên": ["Tân Hợp"],
  },
  "Bắc Giang": {
    "Thành Phố Bắc Giang": ["Thành Phố"],
    "Yên Thế": ["Tân Hợp"],
  },
  "Quảng Ninh": {
    "Thành Phố Hạ Long": ["Thành Phố"],
    "Cẩm Phả": ["Tân Hợp"],
  },
  "Hải Dương": {
    "Thành Phố Hải Dương": ["Thành Phố"],
    "Kinh Môn": ["Tân Hợp"],
  },
  "Hải Phòng": {
    "Hồng Bàng": ["Hồng Bàng"],
    "Ngô Quyền": ["Ngô Quyền"],
    "Lê Chân": ["Lê Chân"],
    "Kiến An": ["Kiến An"],
  },
  "Hưng Yên": {
    "Thành Phố Hưng Yên": ["Thành Phố"],
    "Ân Thi": ["Tân Hợp"],
  },
  "Thái Bình": {
    "Thành Phố Thái Bình": ["Thành Phố"],
    "Kiến Xương": ["Tân Hợp"],
  },
  "Hà Nam": {
    "Thành Phố Phủ Lý": ["Thành Phố"],
    "Duy Tiên": ["Tân Hợp"],
  },
  "Nam Định": {
    "Thành Phố Nam Định": ["Thành Phố"],
    "Trực Ninh": ["Tân Hợp"],
  },
  "Ninh Bình": {
    "Thành Phố Ninh Bình": ["Thành Phố"],
    "Hoa Lư": ["Hoa Lư"],
  },
  "TP. Hồ Chí Minh": {
    "Quận 1": ["Bến Nghé", "Bến Thành", "Cô Giang", "Cầu Kho", "Cầu Ông Lãnh", "Da Kao", "Nguyễn Cư Trinh"],
    "Quận 2": ["An Khánh", "An Phú", "Bạch Đằng", "Cát Lái", "Hiệp Phú", "Phú Nhuận", "Thảo Điền", "Thanh Bình", "Thanh Lộc"],
    "Quận 3": ["Võ Thị Sáu", "Nguyễn Trung Trực", "Calmette", "Phạm Ngũ Lão"],
    "Quận 4": ["Bến Vân Đồn", "Cát Dài", "Long Toàn", "Phú Thuận", "Tân Hưng"],
    "Quận 5": ["Công Quang", "Ghép", "Kiến An", "Phú Thọ", "Tân An"],
    "Quận 6": ["Phú Trung", "Phú Thạnh", "Tân Thành", "Tân Định"],
    "Quận 7": ["Bình Thuận", "Phú Mỹ", "Tân Hưng", "Tân Kiểng", "Tân Phong"],
    "Quận 8": ["An Khánh", "Bình Kiên", "Bình Trị Đông", "Linh Trung", "Linh Xuân"],
    "Quận 9": ["An Phú", "Hiệp Phú", "Long Bình", "Long Phước", "Phước Long A"],
    "Quận 10": ["Cô Giang", "Cầu Kho", "Đa Kao"],
    "Quận 11": ["Phú Thạnh", "Phú Trung", "Tân Thành"],
    "Quận 12": ["Đông Hưng Thuận", "Hiệp Thành", "Tân Chánh Hiệp"],
  },
  "Bình Dương": {
    "Thành Phố Thủ Dầu Một": ["Thành Phố"],
    "Bến Cát": ["Tân Hợp"],
  },
  "Bình Phước": {
    "Thành Phố Đồng Xoài": ["Thành Phố"],
  },
  "Đồng Nai": {
    "Thành Phố Biên Hòa": ["Thành Phố"],
  },
  "Tây Ninh": {
    "Thành Phố Tây Ninh": ["Thành Phố"],
  },
  "An Giang": {
    "Thành Phố Long Xuyên": ["Thành Phố"],
  },
  "Kiên Giang": {
    "Thành Phố Hạ Phát": ["Thành Phố"],
  },
  "Cần Thơ": {
    "Quận 1": ["An Hoà", "An Khánh", "Cái Khế"],
    "Quận 2": ["Bình Thủy", "Cái Khế", "Long Hòa"],
    "Quận 3": ["An Hòa", "Bình Thủy"],
    "Quận 4": ["Cái Khế", "Hòa An"],
    "Quận 5": ["Hòa Bình", "Long Tuyền"],
  },
  "Đà Nẵng": {
    "Hải Châu": ["Bình Hiên", "Bình Thuận", "Hải Châu 1", "Hải Châu 2", "Nại Hiên Đông", "Thanh Bình"],
    "Cẩm Lệ": ["Cẩm Chánh", "Cẩm Lệ"],
    "Ngũ Hành Sơn": ["Mỹ An", "Mỹ Khê"],
    "Liên Chiểu": ["Chính Gián", "Hoà Minh"],
  },
  "Quảng Nam": {
    "Thành Phố Tam Kỳ": ["Thành Phố"],
  },
  "Quảng Ngãi": {
    "Thành Phố Quảng Ngãi": ["Thành Phố"],
  },
  "Bình Định": {
    "Thành Phố Quy Nhơn": ["Thành Phố"],
  },
  "Phú Yên": {
    "Thành Phố Tuy Hòa": ["Thành Phố"],
  },
  "Khánh Hòa": {
    "Thành Phố Nha Trang": ["Thành Phố"],
  },
  "Ninh Thuận": {
    "Thành Phố Phan Rang": ["Thành Phố"],
  },
  "Bình Thuận": {
    "Thành Phố Phan Thiết": ["Thành Phố"],
  },
  "Đắk Lắk": {
    "Thành Phố Buôn Ma Thuột": ["Thành Phố"],
  },
  "Đắk Nông": {
    "Thành Phố Gia Nghĩa": ["Thành Phố"],
  },
  "Lâm Đồng": {
    "Thành Phố Đà Lạt": ["Thành Phố"],
  },
  "Hà Tĩnh": {
    "Thành Phố Hà Tĩnh": ["Thành Phố"],
  },
  "Nghệ An": {
    "Thành Phố Vinh": ["Thành Phố"],
  },
  "Thanh Hóa": {
    "Thành Phố Thanh Hóa": ["Thành Phố"],
  },
  "Hòa Bình": {
    "Thành Phố Hòa Bình": ["Thành Phố"],
  },
  "Sơn La": {
    "Thành Phố Sơn La": ["Thành Phố"],
  },
  "Điện Biên": {
    "Thành Phố Điện Biên Phủ": ["Thành Phố"],
  },
  "Lai Châu": {
    "Thành Phố Lai Châu": ["Thành Phố"],
  },
};

// Function to get provinces
function getProvinces() {
  return Object.keys(vietnamAddresses).sort();
}

// Function to get districts for a province
function getDistricts(province) {
  if (vietnamAddresses[province]) {
    return Object.keys(vietnamAddresses[province]).sort();
  }
  return [];
}

// Function to get wards for a district
function getWards(province, district) {
  if (vietnamAddresses[province] && vietnamAddresses[province][district]) {
    return vietnamAddresses[province][district].sort();
  }
  return [];
}

// Function to initialize address autocomplete
function initAddressAutocomplete() {
  const provinceSelect = document.getElementById('province');
  const districtSelect = document.getElementById('district');
  const wardSelect = document.getElementById('ward');
  const streetInput = document.getElementById('street');
  const addressDisplay = document.getElementById('fullAddress');

  if (!provinceSelect) return;

  // Populate provinces on load
  const provinces = getProvinces();
  provinceSelect.innerHTML = '<option value="">Select Province/City</option>' + 
    provinces.map(p => `<option value="${p}">${p}</option>`).join('');

  // Update districts when province changes
  provinceSelect.addEventListener('change', function() {
    const districts = getDistricts(this.value);
    districtSelect.innerHTML = '<option value="">Select District</option>' + 
      districts.map(d => `<option value="${d}">${d}</option>`).join('');
    wardSelect.innerHTML = '<option value="">Select Ward/Commune</option>';
    updateFullAddress();
  });

  // Update wards when district changes
  districtSelect.addEventListener('change', function() {
    const province = provinceSelect.value;
    const wards = getWards(province, this.value);
    wardSelect.innerHTML = '<option value="">Select Ward/Commune</option>' + 
      wards.map(w => `<option value="${w}">${w}</option>`).join('');
    updateFullAddress();
  });

  // Update full address when any field changes
  wardSelect.addEventListener('change', updateFullAddress);
  streetInput.addEventListener('input', updateFullAddress);

  function updateFullAddress() {
    const province = provinceSelect.value;
    const district = districtSelect.value;
    const ward = wardSelect.value;
    const street = streetInput.value;

    let fullAddress = [];
    if (street) fullAddress.push(street);
    if (ward) fullAddress.push(ward);
    if (district) fullAddress.push(district);
    if (province) fullAddress.push(province);

    addressDisplay.textContent = fullAddress.length > 0 
      ? fullAddress.join(', ') 
      : 'Please select address';
  }
}

// Similar function for checkout form
function initCheckoutAddress() {
  const provinceSelect = document.getElementById('checkoutProvince');
  const districtSelect = document.getElementById('checkoutDistrict');
  const wardSelect = document.getElementById('checkoutWard');
  const streetInput = document.getElementById('checkoutStreet');
  const addressDisplay = document.getElementById('checkoutFullAddress');

  if (!provinceSelect) return;

  // Populate provinces on load
  const provinces = getProvinces();
  provinceSelect.innerHTML = '<option value="">Select Province/City</option>' + 
    provinces.map(p => `<option value="${p}">${p}</option>`).join('');

  // Update districts when province changes
  provinceSelect.addEventListener('change', function() {
    const districts = getDistricts(this.value);
    districtSelect.innerHTML = '<option value="">Select District</option>' + 
      districts.map(d => `<option value="${d}">${d}</option>`).join('');
    wardSelect.innerHTML = '<option value="">Select Ward/Commune</option>';
    updateFullAddress();
  });

  // Update wards when district changes
  districtSelect.addEventListener('change', function() {
    const province = provinceSelect.value;
    const wards = getWards(province, this.value);
    wardSelect.innerHTML = '<option value="">Select Ward/Commune</option>' + 
      wards.map(w => `<option value="${w}">${w}</option>`).join('');
    updateFullAddress();
  });

  // Update full address when any field changes
  wardSelect.addEventListener('change', updateFullAddress);
  streetInput.addEventListener('input', updateFullAddress);

  function updateFullAddress() {
    const province = provinceSelect.value;
    const district = districtSelect.value;
    const ward = wardSelect.value;
    const street = streetInput.value;

    let fullAddress = [];
    if (street) fullAddress.push(street);
    if (ward) fullAddress.push(ward);
    if (district) fullAddress.push(district);
    if (province) fullAddress.push(province);

    addressDisplay.textContent = fullAddress.length > 0 
      ? fullAddress.join(', ') 
      : 'Please select address';
  }
}

// Function to validate address form
function validateAddressForm() {
  const province = document.getElementById('province').value;
  const district = document.getElementById('district').value;
  const ward = document.getElementById('ward').value;

  if (!province) {
    alert('Please select Province/City');
    return false;
  }
  if (!district) {
    alert('Please select District');
    return false;
  }
  if (!ward) {
    alert('Please select Ward/Commune');
    return false;
  }

  // Build full address
  const street = document.getElementById('street').value.trim();
  const fullAddress = [street, ward, district, province].filter(x => x).join(', ');
  
  // Store in hidden field
  document.getElementById('address').value = fullAddress;

  return true;
}

// Function to save checkout address
function saveCheckoutAddress() {
  const province = document.getElementById('checkoutProvince').value;
  const district = document.getElementById('checkoutDistrict').value;
  const ward = document.getElementById('checkoutWard').value;
  const street = document.getElementById('checkoutStreet').value.trim();

  if (!province || !district || !ward) {
    alert('Please select Province/City, District, and Ward/Commune');
    return false;
  }

  // Build full address
  const fullAddress = [street, ward, district, province].filter(x => x).join(', ');
  
  // Store in hidden field
  document.getElementById('checkoutAddress').value = fullAddress;
  
  // Close form
  document.getElementById('newAddressForm').classList.add('hidden');
  
  // Show message
  alert('Address saved successfully');
  
  return true;
}

// Initialize on DOM ready
document.addEventListener('DOMContentLoaded', function() {
  initAddressAutocomplete();
});
