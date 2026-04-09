// Vietnamese Provinces, Districts, and Wards Data - English version
const vietnamAddresses = {
  "Hanoi": {
    "Hoan Kiem District": ["Cua Dong Ward", "Cua Nam Ward", "Cua Bac Ward", "Hang Bac Ward", "Hang Buom Ward", "Hang Gai Ward", "Hang Ngang Ward", "Hang Trong Ward", "Trang Tien Ward"],
    "Ba Dinh District": ["Ba Dinh Ward", "Cong Vi Ward", "Dien Bien Phu Ward", "Hoan Kiem Ward", "Lieu Giai Ward", "Quang An Ward", "Thanh Van Ward"],
    "Hai Ba Trung District": ["Bach Khoa Ward", "Hang Bot Ward", "Kham Thien Ward", "Minh Khai Ward", "Thong Nhat Ward"],
    "Dong Da District": ["Hang Bun Ward", "Kim Ma Ward", "Lang Ward", "Lang Ha Ward", "Phuong Mai Ward", "Trung Liet Ward"],
    "Tay Ho District": ["Cau Giay Ward", "Hai Ba Trung Ward", "Phu Thuong Ward", "Quang An Ward", "Thuy Khue Ward", "Tu Lien Ward", "Tu Hoa Ward", "Vinh Phuc Ward"],
    "Cau Giay District": ["Dich Vong Ward", "Dich Vong Hau Ward", "Hoang Minh Giam Ward", "Lieu Giai Ward", "Nghia Do Ward", "Nghia Tan Ward", "Quan Hoa Ward", "Yen Phu Ward"],
    "Thanh Xuan District": ["Khuong Thuong Ward", "Khuong Dinh Ward", "Phuong Liet Ward", "Phuong Mai Ward", "Thanh Xuan Bac Ward", "Thanh Xuan Nam Ward", "Thanh Xuan Trung Ward"],
    "Hoang Mai District": ["Cau Dien Ward", "Ha Dinh Ward", "Hoang Liet Ward", "Lien Mac Ward", "Mai Dong Ward", "Minh Khai Ward", "Vinh Huong Ward", "Viet Huong Ward"],
    "Long Bien District": ["Gia Thuy Ward", "Giang Bien Ward", "Long Bien Ward", "Thach Ban Ward", "Uyen Huong Ward", "Viet Hung Ward"],
    "Bac Tu Liem District": ["Cau Giay Ward", "Tay Mo Ward", "Thuong Cat Ward", "Xuan Phuong Ward"],
    "Nam Tu Liem District": ["Ha Cau Ward", "Mo Lao Ward", "Tu Liem Ward", "Cau Giay Ward"],
    "Ha Dong District": ["Buon Ward", "Cong Hoang Ward", "Duong Noi Ward", "Ha Cau Ward", "Le Loi Ward", "Minh Khai Ward", "Thanh Am Ward", "Tay Mo Ward"],
    "Son Tay Town": ["Ba Sao Ward", "Chuc Son Ward", "Minh Phu Ward", "Phu Cat Ward"],
  },
  "Ha Giang": {
    "Ha Giang City": ["City Center"],
    "Vi Xuyen District": ["Thuy Toan Ward", "Lung Khe Ward"],
    "Quan Ba District": ["Lung Cang Ward", "Van Chua Ward"],
  },
  "Cao Bang": {
    "Cao Bang City": ["City Center"],
    "Bao Lam District": ["Pac Ha Ward", "Rang Dong Ward"],
  },
  "Bac Kan": {
    "Bac Kan City": ["City Center"],
    "Cho Moi District": ["Tay Hop Ward", "Thung Nham Ward"],
  },
  "Tuyen Quang": {
    "Tuyen Quang City": ["City Center"],
    "Na Hang District": ["Cam Thuong Ward", "Chieu Luong Ward"],
  },
  "Lao Cai": {
    "Lao Cai City": ["City Center"],
    "Sa Pa Town": ["Sa Pa Ward", "Ta Phin Ward"],
  },
  "Yen Bai": {
    "Yen Bai City": ["City Center"],
    "Luc Yen District": ["Tan Hop Ward"],
  },
  "Thai Nguyen": {
    "Thai Nguyen City": ["City Center"],
    "Dinh Hoa District": ["Cam Thuong Ward"],
  },
  "Phu Tho": {
    "Viet Tri City": ["City Center"],
    "Phu Xuyen District": ["Tan Hop Ward"],
  },
  "Vinh Phuc": {
    "Vinh Yen City": ["City Center"],
    "Binh Xuyen District": ["Tan Hop Ward"],
  },
  "Bac Giang": {
    "Bac Giang City": ["City Center"],
    "Yen The District": ["Tan Hop Ward"],
  },
  "Quang Ninh": {
    "Ha Long City": ["City Center"],
    "Cam Pha City": ["Tan Hop Ward"],
  },
  "Hai Duong": {
    "Hai Duong City": ["City Center"],
    "Kinh Mon Town": ["Tan Hop Ward"],
  },
  "Haiphong": {
    "Hong Bang District": ["Hong Bang Ward"],
    "Ngo Quyen District": ["Ngo Quyen Ward"],
    "Le Chan District": ["Le Chan Ward"],
    "Kien An District": ["Kien An Ward"],
  },
  "Hung Yen": {
    "Hung Yen City": ["City Center"],
    "An Thi District": ["Tan Hop Ward"],
  },
  "Thai Binh": {
    "Thai Binh City": ["City Center"],
    "Kien Xuong District": ["Tan Hop Ward"],
  },
  "Ha Nam": {
    "Phu Ly City": ["City Center"],
    "Duy Tien District": ["Tan Hop Ward"],
  },
  "Nam Dinh": {
    "Nam Dinh City": ["City Center"],
    "Truc Ninh District": ["Tan Hop Ward"],
  },
  "Ninh Binh": {
    "Ninh Binh City": ["City Center"],
    "Hoa Lu District": ["Hoa Lu Ward"],
  },
  "Ho Chi Minh City": {
    "District 1": ["Ben Nghe Ward", "Ben Thanh Ward", "Co Giang Ward", "Cau Kho Ward", "Cau Ong Lanh Ward", "Da Kao Ward", "Nguyen Cu Trinh Ward"],
    "District 2": ["An Khanh Ward", "An Phu Ward", "Bach Dang Ward", "Cat Lai Ward", "Hiep Phu Ward", "Phu Nhuan Ward", "Thao Dien Ward", "Thanh Binh Ward", "Thanh Loc Ward"],
    "District 3": ["Vo Thi Sau Ward", "Nguyen Trung Truc Ward", "Calmette Ward", "Pham Ngu Lao Ward"],
    "District 4": ["Ben Van Don Ward", "Cat Dai Ward", "Long Toan Ward", "Phu Thuan Ward", "Tan Hung Ward"],
    "District 5": ["Cong Quang Ward", "Ghep Ward", "Kien An Ward", "Phu Tho Ward", "Tan An Ward"],
    "District 6": ["Phu Trung Ward", "Phu Thanh Ward", "Tan Thanh Ward", "Tan Dinh Ward"],
    "District 7": ["Binh Thuan Ward", "Phu My Ward", "Tan Hung Ward", "Tan Kieng Ward", "Tan Phong Ward"],
    "District 8": ["An Khanh Ward", "Binh Kien Ward", "Binh Tri Dong Ward", "Linh Trung Ward", "Linh Xuan Ward"],
    "District 9": ["An Phu Ward", "Hiep Phu Ward", "Long Binh Ward", "Long Phuoc Ward", "Phuoc Long A Ward"],
    "District 10": ["Co Giang Ward", "Cau Kho Ward", "Da Kao Ward"],
    "District 11": ["Phu Thanh Ward", "Phu Trung Ward", "Tan Thanh Ward"],
    "District 12": ["Dong Hung Thuan Ward", "Hiep Thanh Ward", "Tan Chanh Hiep Ward"],
  },
  "Binh Duong": {
    "Thu Dau Mot City": ["City Center"],
    "Ben Cat Town": ["Tan Hop Ward"],
  },
  "Binh Phuoc": {
    "Dong Xoai City": ["City Center"],
  },
  "Dong Nai": {
    "Bien Hoa City": ["City Center"],
  },
  "Tay Ninh": {
    "Tay Ninh City": ["City Center"],
  },
  "An Giang": {
    "Long Xuyen City": ["City Center"],
  },
  "Kien Giang": {
    "Rach Gia City": ["City Center"],
  },
  "Can Tho": {
    "Ninh Kieu District": ["An Hoa Ward", "An Khanh Ward", "Cai Khe Ward"],
    "Binh Thuy District": ["Binh Thuy Ward", "Cai Khe Ward", "Long Hoa Ward"],
    "Cai Rang District": ["An Hoa Ward", "Binh Thuy Ward"],
    "O Mon District": ["Cai Khe Ward", "Hoa An Ward"],
    "Thot Not District": ["Hoa Binh Ward", "Long Tuyen Ward"],
  },
  "Da Nang": {
    "Hai Chau District": ["Binh Hien Ward", "Binh Thuan Ward", "Hai Chau 1 Ward", "Hai Chau 2 Ward", "Nai Hien Dong Ward", "Thanh Binh Ward"],
    "Cam Le District": ["Cam Chanh Ward", "Cam Le Ward"],
    "Ngu Hanh Son District": ["My An Ward", "My Khe Ward"],
    "Lien Chieu District": ["Chinh Gian Ward", "Hoa Minh Ward"],
  },
  "Quang Nam": {
    "Tam Ky City": ["City Center"],
  },
  "Quang Ngãi": {
    "Quang Ngai City": ["City Center"],
  },
  "Binh Dinh": {
    "Quy Nhon City": ["City Center"],
  },
  "Phu Yen": {
    "Tuy Hoa City": ["City Center"],
  },
  "Khanh Hoa": {
    "Nha Trang City": ["City Center"],
  },
  "Ninh Thuan": {
    "Phan Rang City": ["City Center"],
  },
  "Binh Thuan": {
    "Phan Thiet City": ["City Center"],
  },
  "Dak Lak": {
    "Buon Ma Thuot City": ["City Center"],
  },
  "Dak Nong": {
    "Gia Nghia City": ["City Center"],
  },
  "Lam Dong": {
    "Da Lat City": ["City Center"],
  },
  "Ha Tinh": {
    "Ha Tinh City": ["City Center"],
  },
  "Nghe An": {
    "Vinh City": ["City Center"],
  },
  "Thanh Hoa": {
    "Thanh Hoa City": ["City Center"],
  },
  "Hoa Binh": {
    "Hoa Binh City": ["City Center"],
  },
  "Son La": {
    "Son La City": ["City Center"],
  },
  "Dien Bien": {
    "Dien Bien Phu City": ["City Center"],
  },
  "Lai Chau": {
    "Lai Chau City": ["City Center"],
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
function validateAddressForm(event) {
  const province = document.getElementById('province').value;
  const district = document.getElementById('district').value;
  const ward = document.getElementById('ward').value;

  if (!province || !district || !ward) {
    alert('Please select Province/City, District, and Ward/Commune');
    if (event) event.preventDefault();
    return false;
  }

  // Build full address
  const street = document.getElementById('street').value.trim();
  const fullAddress = [street, ward, district, province].filter(x => x).join(', ');
  
  // Store in hidden field
  document.getElementById('address').value = fullAddress;
  return true;
}

// Also handle checkout form validation
function validateCheckoutForm(event) {
  // If user is adding a new address during checkout
  const addressId = document.querySelector('input[name="address_id"]:checked')?.value;
  
  if (!addressId) {
    const province = document.getElementById('checkoutProvince').value;
    const district = document.getElementById('checkoutDistrict').value;
    const ward = document.getElementById('checkoutWard').value;
    const street = document.getElementById('checkoutStreet').value;
    const name = document.querySelector('input[name="recipient_name"]').value;
    const phone = document.querySelector('input[name="recipient_phone"]').value;

    if (!province || !district || !ward || !street || !name || !phone) {
      alert('Please provide complete delivery address information');
      if (event) event.preventDefault();
      return false;
    }

    const fullAddress = [street, ward, district, province].filter(x => x).join(', ');
    document.getElementById('checkoutAddressHidden').value = fullAddress;
  }
  return true;
}

// Run initializers
document.addEventListener('DOMContentLoaded', () => {
  initAddressAutocomplete();
  initCheckoutAddress();
});
