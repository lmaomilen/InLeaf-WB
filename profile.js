$(document).ready(function () {

  $("#toggleAddressForm").click(function () {
    $("#addAddressForm").toggleClass("hidden");
  });
  $("#toggleUserUpdate").click(function () {
    $("#userUpdateWrapper").toggleClass("hidden");
  });


  $("#addAddressForm").submit(function (e) {
    e.preventDefault();

    $.post("profile_handler.php", $(this).serialize(), function (res) {
      if (res.status === "success") {
        location.reload();
      } else {
        alert(res.message);
      }
    }, 'json');
  });

  $(".delete-address").click(function () {
    if (!confirm("Delete this address?")) return;

    let id = $(this).data("id");

    $.post("profile_handler.php", { delete_address_id: id }, function (res) {
      if (res.status === "success") {
        location.reload();
      } else {
        alert(res.message);
      }
    }, 'json');
  });

  $(".update-address").click(function () {
    let form = $(this).closest("form");
    let id = form.data("id");
    let data = {
      update_address_id: id,
      street: form.find("input[name=street]").val(),
      city: form.find("input[name=city]").val(),
      postal_code: form.find("input[name=postal_code]").val(),
      country: form.find("input[name=country]").val()
    };

    $.post("profile_handler.php", data, function (res) {
      if (res.status === "success") {
        alert("Address updated");
      } else {
        alert(res.message);
      }
    }, 'json');
  });


  $("#userUpdateForm").submit(function (e) {
    e.preventDefault();

    $.ajax({
      url: "profile_handler.php",
      type: "POST",
      data: $(this).serialize(),
      dataType: "json",
      success: function (res) {
        if (res.status === "success") {
          alert("Profile updated!");
          location.reload();
        } else {
          alert(res.message);
        }
      },
      error: function () {
        alert("Something went wrong");
      }
    });
  });
});

document.querySelectorAll('.delete-order-form').forEach(form => {
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        if (!confirm("Are you sure you want to delete this order?")) return;

        const formData = new FormData(this);
        const response = await fetch('/progetto/profile/Profile_handler.php', {
            method: 'POST',
            body: formData
        });

        const result = await response.json();

        if (result.status === 'success') {
            alert("Order deleted successfully!");
            location.reload();
        } else {
            alert("Error: " + result.message);
        }
    });
});


$("#avatarForm").on("submit", function (e) {
  e.preventDefault();

  const formData = new FormData(this);

  $.ajax({
    url: "profile_handler.php",
    type: "POST",
    data: formData,
    contentType: false,
    processData: false,
    dataType: "json",
    success: function (res) {
      if (res.status === "success") {
        $("#userAvatar").attr("src", res.avatar); 
        alert("Avatar updated!");
      } else {
        alert("Error: " + res.message);
        if (res.debug) console.log("Debug:", res.debug);
      }
    },
    error: function (xhr, status, err) {
      alert("AJAX upload failed");
      console.error("Error:", err);
    }
  });
});

