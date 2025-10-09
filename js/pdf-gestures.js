document.addEventListener("DOMContentLoaded", function () {
  const viewer = document.getElementById("pdfViewer");
  const hammer = new Hammer.Manager(viewer);

  hammer.add(new Hammer.Swipe({ direction: Hammer.DIRECTION_HORIZONTAL }));

  hammer.on("swipeleft", () => {
    if (typeof goToNextPage === "function") goToNextPage();
  });

  hammer.on("swiperight", () => {
    if (typeof goToPreviousPage === "function") goToPreviousPage();
  });

  hammer.add(new Hammer.Pinch());
  let currentScale = 1;

  hammer.on("pinch", e => {
    currentScale = Math.min(Math.max(e.scale, 0.5), 3);
    viewer.style.transform = `scale(${currentScale})`;
    viewer.style.transformOrigin = "center center";
  });
});
