<script>
    if (top != self) {
        console.log("Breaking free from iFrame to show errors");
        top.location = self.location.href;
    }
</script>